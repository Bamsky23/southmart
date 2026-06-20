<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Helpers\DatabaseHelper;
use App\Models\Branch;
use App\Models\Product;
use App\Models\Category;
use App\Models\Transaction;

class CashierController extends Controller
{
    /**
     * Display the POS checkout interface.
     */
    public function pos()
    {
        $user = Auth::user();
        $branchId = $user->branch_id;
        $branchName = DatabaseHelper::getBranchName($branchId);
        $conn = DatabaseHelper::getConnectionName($branchId);

        // Fetch products and their stock for this branch from the branch database
        $products = [];
        try {
            $products = DB::connection($conn)->table('products')
                ->join('inventory', 'products.id', '=', 'inventory.product_id')
                ->where('inventory.branch_id', $branchId)
                ->whereNull('products.deleted_at')
                ->select('products.*', 'inventory.stock')
                ->get();
        } catch (\Exception $e) {
            // If connection fails, show empty or error
        }

        // Get status sync count
        $pendingCount = 0;
        try {
            $pendingCount = DB::connection($conn)->table('transactions')
                ->where('sync_status', 'pending')
                ->count();
        } catch (\Exception $e) {}

        // Check if node is online
        $isOnline = DatabaseHelper::isNodeOnline($branchId);

        // Load categories from branch connection (fallback to central)
        $categories = collect();
        try {
            $categories = DB::connection($conn)->table('categories')->orderBy('id')->get();
        } catch (\Exception $e) {
            $categories = Category::orderBy('id')->get();
        }

        return view('kasir.pos', compact('branchName', 'products', 'pendingCount', 'isOnline', 'categories'));
    }

    /**
     * Process checkout transaction.
     */
    public function checkout(Request $request)
    {
        $user = Auth::user();
        $branchId = $user->branch_id;
        $conn = DatabaseHelper::getConnectionName($branchId);

        $request->validate([
            'cart' => 'required|array',
            'payment_method' => 'required|string',
            'amount_paid' => 'required|numeric|min:0',
        ]);

        $cart = $request->cart;
        $method = $request->payment_method;
        $amountPaid = $request->amount_paid;

        try {
            $txCode = null;

            // Open transaction on branch database
            DB::connection($conn)->transaction(function () use ($conn, $branchId, $user, $cart, $method, $amountPaid, &$txCode) {
                // 1. Calculate totals
                $subtotal = 0;
                foreach ($cart as $item) {
                    $subtotal += $item['price'] * $item['qty'];
                }

                $tax = round($subtotal * 0.11); // 11% PPN
                $grandTotal = $subtotal + $tax;
                $change = $amountPaid - $grandTotal;

                if ($change < 0 && $method === 'tunai') {
                    throw new \Exception("Uang bayar tidak mencukupi.");
                }

                // If non-cash, exact amount is expected
                if ($method !== 'tunai') {
                    $amountPaid = $grandTotal;
                    $change = 0;
                }

                $branchCode = DB::connection($conn)->table('branches')->where('id', $branchId)->value('code') ?? 'NODE';
                $txCode = "TX-{$branchCode}-" . now()->format('YmdHis') . "-" . rand(1000, 9999);

                // 2. Insert Transaction
                $txId = DB::connection($conn)->table('transactions')->insertGetId([
                    'transaction_code' => $txCode,
                    'branch_id' => $branchId,
                    'user_id' => $user->id,
                    'total_price' => $subtotal,
                    'discount' => 0,
                    'tax' => $tax,
                    'grand_total' => $grandTotal,
                    'payment_status' => 'completed',
                    'sync_status' => 'pending', // Default pending, we'll try to sync next
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // 3. Insert Details & Deduct Stock
                foreach ($cart as $item) {
                    $productId = $item['id'];
                    $qty = $item['qty'];

                    // Insert detail
                    DB::connection($conn)->table('transaction_details')->insert([
                        'transaction_id' => $txId,
                        'product_id' => $productId,
                        'quantity' => $qty,
                        'price' => $item['price'],
                        'subtotal' => $item['price'] * $qty,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // Deduct stock on local node
                    $currentStock = DB::connection($conn)->table('inventory')
                        ->where('product_id', $productId)
                        ->where('branch_id', $branchId)
                        ->value('stock') ?? 0;

                    $newStock = max(0, $currentStock - $qty);

                    DB::connection($conn)->table('inventory')
                        ->where('product_id', $productId)
                        ->where('branch_id', $branchId)
                        ->update(['stock' => $newStock, 'updated_at' => now()]);

                    // Log stock movement
                    DB::connection($conn)->table('stock_movements')->insert([
                        'product_id' => $productId,
                        'branch_id' => $branchId,
                        'type' => 'out',
                        'quantity' => $qty,
                        'reference' => "POS Sale {$txCode}",
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // 4. Insert Payment
                DB::connection($conn)->table('payments')->insert([
                    'transaction_id' => $txId,
                    'method' => $method,
                    'amount_paid' => $amountPaid,
                    'amount_change' => $change,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // 5. Insert Receipt
                DB::connection($conn)->table('receipts')->insert([
                    'transaction_id' => $txId,
                    'receipt_code' => 'REC-' . $txCode,
                    'printed_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // 6. Log Activity locally
                DB::connection($conn)->table('activity_logs')->insert([
                    'branch_id' => $branchId,
                    'user_id' => $user->id,
                    'activity' => 'Transaksi POS',
                    'description' => "Penjualan Baru {$txCode} sebesar Rp " . number_format($grandTotal, 0, ',', '.'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // 7. Replicate stock deduction to Central Immediately
                try {
                    // Update Central inventory stock as well since Central tracks all stock levels
                    // We directly update it if Central connection is available
                    $inventories = DB::connection($conn)->table('inventory')->where('branch_id', $branchId)->get();
                    foreach ($inventories as $inv) {
                        DB::connection('mysql')->table('inventory')->updateOrInsert(
                            ['product_id' => $inv->product_id, 'branch_id' => $branchId],
                            ['stock' => $inv->stock, 'updated_at' => now()]
                        );
                    }
                } catch (\Exception $e) {
                    // Ignore if central is unreachable
                }
            });

            // Try to sync this transaction to central immediately if branch is online
            if (DatabaseHelper::isNodeOnline($branchId)) {
                try {
                    // Copy to central database
                    $txNode = DB::connection($conn)->table('transactions')->where('transaction_code', $txCode)->first();
                    $detailsNode = DB::connection($conn)->table('transaction_details')->where('transaction_id', $txNode->id)->get();
                    $paymentNode = DB::connection($conn)->table('payments')->where('transaction_id', $txNode->id)->first();
                    $receiptNode = DB::connection($conn)->table('receipts')->where('transaction_id', $txNode->id)->first();

                    DB::transaction(function () use ($txNode, $detailsNode, $paymentNode, $receiptNode, $conn, $branchId, $user) {
                        // Insert transaction in Central
                        $centralTxId = DB::connection('mysql')->table('transactions')->insertGetId([
                            'transaction_code' => $txNode->transaction_code,
                            'branch_id' => $txNode->branch_id,
                            'user_id' => $txNode->user_id,
                            'total_price' => $txNode->total_price,
                            'discount' => $txNode->discount,
                            'tax' => $txNode->tax,
                            'grand_total' => $txNode->grand_total,
                            'payment_status' => $txNode->payment_status,
                            'sync_status' => 'synced',
                            'created_at' => $txNode->created_at,
                            'updated_at' => $txNode->updated_at,
                        ]);

                        foreach ($detailsNode as $detail) {
                            DB::connection('mysql')->table('transaction_details')->insert([
                                'transaction_id' => $centralTxId,
                                'product_id' => $detail->product_id,
                                'quantity' => $detail->quantity,
                                'price' => $detail->price,
                                'subtotal' => $detail->subtotal,
                                'created_at' => $detail->created_at,
                                'updated_at' => $detail->updated_at,
                            ]);
                        }

                        if ($paymentNode) {
                            DB::connection('mysql')->table('payments')->insert([
                                'transaction_id' => $centralTxId,
                                'method' => $paymentNode->method,
                                'amount_paid' => $paymentNode->amount_paid,
                                'amount_change' => $paymentNode->amount_change,
                                'created_at' => $paymentNode->created_at,
                                'updated_at' => $paymentNode->updated_at,
                            ]);
                        }

                        if ($receiptNode) {
                            DB::connection('mysql')->table('receipts')->insert([
                                'transaction_id' => $centralTxId,
                                'receipt_code' => $receiptNode->receipt_code,
                                'printed_at' => $receiptNode->printed_at,
                                'created_at' => $receiptNode->created_at,
                                'updated_at' => $receiptNode->updated_at,
                            ]);
                        }

                        // Log activity in Central
                        DB::connection('mysql')->table('activity_logs')->insert([
                            'branch_id' => $branchId,
                            'user_id' => $user->id,
                            'activity' => 'Transaksi POS (Synced)',
                            'description' => "Penjualan Terdistribusi {$txNode->transaction_code} berhasil disinkronisasi ke pusat.",
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    });

                    // Mark synced locally
                    DB::connection($conn)->table('transactions')
                        ->where('transaction_code', $txCode)
                        ->update(['sync_status' => 'synced']);

                } catch (\Exception $e) {
                    // Sync failed, let it remain pending
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil diproses.',
                'transaction_code' => $txCode
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi gagal: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show receipt details.
     */
    public function receipt($transactionCode)
    {
        $user = Auth::user();
        $branchId = $user->branch_id;
        $conn = DatabaseHelper::getConnectionName($branchId);

        // Fetch local transaction
        $tx = DB::connection($conn)->table('transactions')
            ->where('transaction_code', $transactionCode)
            ->first();

        if (!$tx) {
            abort(404, 'Transaksi tidak ditemukan.');
        }

        $details = DB::connection($conn)->table('transaction_details')
            ->join('products', 'transaction_details.product_id', '=', 'products.id')
            ->where('transaction_details.transaction_id', $tx->id)
            ->select('transaction_details.*', 'products.name as product_name', 'products.barcode')
            ->get();

        $payment = DB::connection($conn)->table('payments')
            ->where('transaction_id', $tx->id)
            ->first();

        $branchName = DatabaseHelper::getBranchName($branchId);

        return view('kasir.receipt', compact('tx', 'details', 'payment', 'branchName'));
    }

    /**
     * Download receipt PDF.
     */
    public function downloadReceipt($transactionCode)
    {
        // For coursework demonstration, redirect or return a PDF mock download
        // (Similar to report downloads)
        return redirect()->away('https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf');
    }

    /**
     * Transaction history on this branch.
     */
    public function history()
    {
        $user = Auth::user();
        $branchId = $user->branch_id;
        $conn = DatabaseHelper::getConnectionName($branchId);
        $branchName = DatabaseHelper::getBranchName($branchId);

        $transactions = DB::connection($conn)->table('transactions')
            ->leftJoin('payments', 'transactions.id', '=', 'payments.transaction_id')
            ->select('transactions.*', 'payments.method as payment_method')
            ->orderBy('transactions.created_at', 'desc')
            ->paginate(15);

        return view('kasir.history', compact('transactions', 'branchName'));
    }

    /**
     * Sync local pending transactions to Central.
     */
    public function syncLocalTransactions()
    {
        $user = Auth::user();
        $branchId = $user->branch_id;

        if (!DatabaseHelper::isNodeOnline($branchId)) {
            return back()->with('error', 'Gagal: Jaringan sedang offline. Tidak dapat sinkronisasi.');
        }

        $syncService = app(SyncService::class);
        $results = $syncService->syncTransactions();

        return back()->with('success', 'Sinkronisasi transaksi berhasil dilaksanakan.');
    }

    /**
     * Void the latest transaction for the current cashier.
     */
    public function voidLatestTransaction(Request $request)
    {
        $user = Auth::user();
        $branchId = $user->branch_id;
        $conn = DatabaseHelper::getConnectionName($branchId);

        try {
            // Find latest transaction of the current cashier
            $tx = DB::connection($conn)->table('transactions')
                ->where('branch_id', $branchId)
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$tx) {
                return back()->with('error', 'Tidak ada transaksi terbaru untuk dibatalkan.');
            }

            // Perform rollback and deletion inside a transaction
            DB::connection($conn)->transaction(function () use ($conn, $branchId, $tx) {
                // 1. Get all transaction details
                $details = DB::connection($conn)->table('transaction_details')
                    ->where('transaction_id', $tx->id)
                    ->get();

                // 2. Restore stock for each product
                foreach ($details as $detail) {
                    $productId = $detail->product_id;
                    $qty = $detail->quantity;

                    // Get current stock
                    $currentStock = DB::connection($conn)->table('inventory')
                        ->where('product_id', $productId)
                        ->where('branch_id', $branchId)
                        ->value('stock') ?? 0;

                    $newStock = $currentStock + $qty;

                    // Update stock locally
                    DB::connection($conn)->table('inventory')
                        ->where('product_id', $productId)
                        ->where('branch_id', $branchId)
                        ->update(['stock' => $newStock, 'updated_at' => now()]);

                    // Add stock movement log
                    DB::connection($conn)->table('stock_movements')->insert([
                        'product_id' => $productId,
                        'branch_id' => $branchId,
                        'type' => 'in',
                        'quantity' => $qty,
                        'reference' => "POS Void/Undo {$tx->transaction_code}",
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // 3. Delete receipt, payment, transaction details, and transaction locally
                DB::connection($conn)->table('receipts')->where('transaction_id', $tx->id)->delete();
                DB::connection($conn)->table('payments')->where('transaction_id', $tx->id)->delete();
                DB::connection($conn)->table('transaction_details')->where('transaction_id', $tx->id)->delete();
                DB::connection($conn)->table('transactions')->where('id', $tx->id)->delete();

                // 4. Log the void activity locally
                DB::connection($conn)->table('activity_logs')->insert([
                    'branch_id' => $branchId,
                    'user_id' => Auth::user()->id,
                    'activity' => 'Void Transaksi',
                    'description' => "Membatalkan Transaksi {$tx->transaction_code} sebesar Rp " . number_format($tx->grand_total, 0, ',', '.'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });

            // 5. If central is online, revert on Central database
            if (DatabaseHelper::isNodeOnline($branchId)) {
                try {
                    // Try to find the transaction in central database
                    $centralTx = DB::connection('mysql')->table('transactions')
                        ->where('transaction_code', $tx->transaction_code)
                        ->first();

                    if ($centralTx) {
                        DB::connection('mysql')->transaction(function () use ($centralTx, $branchId, $tx) {
                            // Fetch central details to restore central stock levels
                            $centralDetails = DB::connection('mysql')->table('transaction_details')
                                ->where('transaction_id', $centralTx->id)
                                ->get();

                            foreach ($centralDetails as $detail) {
                                $centralStock = DB::connection('mysql')->table('inventory')
                                    ->where('product_id', $detail->product_id)
                                    ->where('branch_id', $branchId)
                                    ->value('stock') ?? 0;

                                DB::connection('mysql')->table('inventory')
                                    ->where('product_id', $detail->product_id)
                                    ->where('branch_id', $branchId)
                                    ->update([
                                        'stock' => $centralStock + $detail->quantity,
                                        'updated_at' => now()
                                    ]);
                            }

                            // Delete related rows in central
                            DB::connection('mysql')->table('receipts')->where('transaction_id', $centralTx->id)->delete();
                            DB::connection('mysql')->table('payments')->where('transaction_id', $centralTx->id)->delete();
                            DB::connection('mysql')->table('transaction_details')->where('transaction_id', $centralTx->id)->delete();
                            DB::connection('mysql')->table('transactions')->where('id', $centralTx->id)->delete();

                            // Log void in Central
                            DB::connection('mysql')->table('activity_logs')->insert([
                                'branch_id' => $branchId,
                                'user_id' => Auth::user()->id,
                                'activity' => 'Void Transaksi (Synced)',
                                'description' => "Pembatalan Transaksi {$tx->transaction_code} berhasil disinkronisasi ke pusat.",
                                'created_at' => now(),
                                	'updated_at' => now(),
                            ]);
                        });
                    } else {
                        // The transaction might have been pending and not synced to central yet.
                        // However, we should still update central stock levels because stock updates might have been replicated during checkout.
                        // Let's do that:
                        $branchConn = DatabaseHelper::getConnectionName($branchId);
                        $inventories = DB::connection($branchConn)->table('inventory')->where('branch_id', $branchId)->get();
                        foreach ($inventories as $inv) {
                            DB::connection('mysql')->table('inventory')->updateOrInsert(
                                ['product_id' => $inv->product_id, 'branch_id' => $branchId],
                                ['stock' => $inv->stock, 'updated_at' => now()]
                            );
                        }
                    }
                } catch (\Exception $e) {
                    // Ignore or log error
                }
            }

            return back()->with('success', 'Transaksi ' . $tx->transaction_code . ' berhasil dibatalkan (void) dan stok dikembalikan.');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membatalkan transaksi: ' . $e->getMessage());
        }
    }
}
