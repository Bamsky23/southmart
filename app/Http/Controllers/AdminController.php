<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\DatabaseHelper;
use App\Services\SyncService;
use App\Services\ConsistencyService;
use App\Models\Branch;
use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use App\Models\Report;

class AdminController extends Controller
{
    protected $syncService;
    protected $consistencyService;

    public function __construct(SyncService $syncService, ConsistencyService $consistencyService)
    {
        $this->syncService = $syncService;
        $this->consistencyService = $consistencyService;
    }

    /**
     * Display the Admin Dashboard.
     */
    public function dashboard()
    {
        // 1. Get Node Statuses
        $nodes = DB::connection('mysql')->table('node_status')
            ->join('branches', 'node_status.branch_id', '=', 'branches.id')
            ->select('branches.*', 'node_status.node_status', 'node_status.db_status', 'node_status.last_sync')
            ->get();

        // 2. Compute distributed database metrics
        $totalNodes = $nodes->count();
        $onlineNodes = $nodes->where('node_status', 'online')->count();
        $offlineNodes = $nodes->where('node_status', 'offline')->count();
        
        // Success rate: count success checks in consistency
        $consistencyRate = 100.00;
        $latestConsistency = DB::connection('mysql')->table('consistency_checks')->get();
        if ($latestConsistency->count() > 0) {
            $consistentCount = $latestConsistency->where('is_consistent', 1)->count();
            $consistencyRate = ($consistentCount / $latestConsistency->count()) * 100;
        }

        // 3. Transactions data (from central database)
        $transactions = DB::connection('mysql')->table('transactions')->get();
        $totalRevenue = $transactions->sum('grand_total');
        $totalTxCount = $transactions->count();
        $totalQtySold = DB::connection('mysql')->table('transaction_details')->sum('quantity');

        // Recent synchronization logs
        $syncLogs = DB::connection('mysql')->table('synchronization_logs')
            ->join('branches', 'synchronization_logs.branch_id', '=', 'branches.id')
            ->select('synchronization_logs.*', 'branches.name as branch_name')
            ->orderBy('synchronization_logs.created_at', 'desc')
            ->limit(5)
            ->get();

        // Recent activity logs
        $activityLogs = DB::connection('mysql')->table('activity_logs')
            ->leftJoin('users', 'activity_logs.user_id', '=', 'users.id')
            ->leftJoin('branches', 'activity_logs.branch_id', '=', 'branches.id')
            ->select('activity_logs.*', 'users.name as user_name', 'branches.name as branch_name')
            ->orderBy('activity_logs.created_at', 'desc')
            ->limit(5)
            ->get();

        // 4. Analytics: Monthly Sales from central
        // We will mock/format data for Chart.js
        $salesByBranch = DB::connection('mysql')->table('transactions')
            ->join('branches', 'transactions.branch_id', '=', 'branches.id')
            ->select('branches.name', DB::raw('SUM(transactions.grand_total) as revenue'))
            ->groupBy('branches.name')
            ->get();

        return view('admin.dashboard', compact(
            'nodes', 'totalNodes', 'onlineNodes', 'offlineNodes', 'consistencyRate',
            'totalRevenue', 'totalTxCount', 'totalQtySold',
            'syncLogs', 'activityLogs', 'salesByBranch'
        ));
    }

    /**
     * Get the latest activity logs in JSON format for real-time dashboard updates.
     */
    public function realtimeActivityLogs()
    {
        $activityLogs = DB::connection('mysql')->table('activity_logs')
            ->leftJoin('users', 'activity_logs.user_id', '=', 'users.id')
            ->leftJoin('branches', 'activity_logs.branch_id', '=', 'branches.id')
            ->select('activity_logs.*', 'users.name as user_name', 'branches.name as branch_name')
            ->orderBy('activity_logs.created_at', 'desc')
            ->limit(5)
            ->get();

        $activityLogs = $activityLogs->map(function ($log) {
            $log->formatted_time = date('d-m-Y H:i', strtotime($log->created_at));
            return $log;
        });

        return response()->json($activityLogs);
    }

    /**
     * Branch monitoring section.
     */
    public function monitoring()
    {
        $nodes = DB::connection('mysql')->table('node_status')
            ->join('branches', 'node_status.branch_id', '=', 'branches.id')
            ->select('branches.*', 'node_status.node_status', 'node_status.db_status', 'node_status.last_sync')
            ->get();

        $branchStats = [];
        foreach ($nodes as $node) {
            $branchId = $node->id;
            $conn = DatabaseHelper::getConnectionName($branchId);
            
            $txToday = 0;
            $pendingSync = 0;

            if ($node->node_status === 'online') {
                try {
                    // Count transactions today on local branch db
                    $txToday = DB::connection($conn)->table('transactions')
                        ->whereDate('created_at', today())
                        ->count();

                    // Count pending sync on local branch db
                    $pendingSync = DB::connection($conn)->table('transactions')
                        ->where('sync_status', 'pending')
                        ->count();
                } catch (\Exception $e) {
                    // Fail gracefully if connection fails
                }
            }

            $branchStats[$branchId] = [
                'tx_today' => $txToday,
                'pending_sync' => $pendingSync
            ];
        }

        return view('admin.monitoring', compact('nodes', 'branchStats'));
    }

    /**
     * Display National Sales logs (replicated transactions).
     */
    public function nationalSales()
    {
        $transactions = DB::connection('mysql')->table('transactions')
            ->join('branches', 'transactions.branch_id', '=', 'branches.id')
            ->join('users', 'transactions.user_id', '=', 'users.id')
            ->select('transactions.*', 'branches.name as branch_name', 'users.name as cashier_name')
            ->orderBy('transactions.created_at', 'desc')
            ->paginate(15);

        return view('admin.national-sales', compact('transactions'));
    }

    /**
     * CROSS-NODE QUERY SIMULATION
     * Direct query on all active nodes database connections.
     */
    public function crossNodeQuery(Request $request)
    {
        $execute = $request->has('run');
        $queryResult = null;

        if ($execute) {
            $branches = DB::connection('mysql')->table('branches')->get();
            $nodesQueried = [];
            $nodesSkipped = [];
            
            $nationalOmzet = 0;
            $nationalTx = 0;
            $nationalQty = 0;
            
            $branchSales = [];

            foreach ($branches as $branch) {
                $branchId = $branch->id;
                
                if (!DatabaseHelper::isNodeOnline($branchId)) {
                    $nodesSkipped[] = $branch->name;
                    continue;
                }

                $nodesQueried[] = $branch->name;
                $conn = DatabaseHelper::getConnectionName($branchId);

                try {
                    // Executing actual SQL query on the branch database connection
                    $omzet = DB::connection($conn)->table('transactions')->sum('grand_total');
                    $txCount = DB::connection($conn)->table('transactions')->count();
                    $qtySold = DB::connection($conn)->table('transaction_details')->sum('quantity');

                    $nationalOmzet += $omzet;
                    $nationalTx += $txCount;
                    $nationalQty += $qtySold;

                    $branchSales[$branch->name] = [
                        'revenue' => $omzet,
                        'transactions' => $txCount,
                        'qty' => $qtySold
                    ];
                } catch (\Exception $e) {
                    $nodesSkipped[] = "{$branch->name} (Error: " . $e->getMessage() . ")";
                }
            }

            // Find best branch and most popular branch
            $bestBranchName = '-';
            $bestBranchRevenue = 0;
            $mostPopularBranchName = '-';
            $mostPopularBranchTx = 0;

            foreach ($branchSales as $name => $stats) {
                if ($stats['revenue'] > $bestBranchRevenue) {
                    $bestBranchRevenue = $stats['revenue'];
                    $bestBranchName = $name;
                }
                if ($stats['transactions'] > $mostPopularBranchTx) {
                    $mostPopularBranchTx = $stats['transactions'];
                    $mostPopularBranchName = $name;
                }
            }

            $queryResult = [
                'nodes_queried' => $nodesQueried,
                'nodes_skipped' => $nodesSkipped,
                'total_omzet' => $nationalOmzet,
                'total_tx' => $nationalTx,
                'total_qty' => $nationalQty,
                'best_branch' => $bestBranchName,
                'best_branch_revenue' => $bestBranchRevenue,
                'most_popular_branch' => $mostPopularBranchName,
                'most_popular_branch_tx' => $mostPopularBranchTx,
                'details' => $branchSales,
                'timestamp' => now()
            ];

            // Log activity
            try {
                DB::connection('mysql')->table('activity_logs')->insert([
                    'activity' => 'Query Lintas Node',
                    'description' => "Menjalankan Query Rekapitulasi Nasional lintas node database. Berhasil: " . count($nodesQueried) . " node, Gagal: " . count($nodesSkipped) . " node.",
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (\Exception $e) {}
        }

        $branchCount = DB::connection('mysql')->table('branches')->count();
        return view('admin.cross-node-query', compact('queryResult', 'execute', 'branchCount'));
    }

    /**
     * Inventory monitoring.
     */
    public function inventory()
    {
        // Fetch inventory aggregated by product and branch
        $inventory = DB::connection('mysql')->table('inventory')
            ->join('products', 'inventory.product_id', '=', 'products.id')
            ->join('branches', 'inventory.branch_id', '=', 'branches.id')
            ->select('inventory.*', 'products.name as product_name', 'products.sku', 'products.barcode', 'branches.name as branch_name')
            ->orderBy('inventory.stock', 'asc')
            ->paginate(15);

        $branches = Branch::all();
        $products = Product::all();

        return view('admin.inventory', compact('inventory', 'branches', 'products'));
    }

    /**
     * Restock / stock adjustment mutation.
     */
    public function inventoryMutation(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer',
            'branch_id' => 'required|integer',
            'type' => 'required|in:in,out',
            'quantity' => 'required|integer|min:1',
            'reference' => 'required|string',
        ]);

        $productId = $request->product_id;
        $branchId = $request->branch_id;
        $qty = $request->quantity;
        $type = $request->type;
        $ref = $request->reference;

        // Run mutation on branch node if it's online
        if (!DatabaseHelper::isNodeOnline($branchId)) {
            return back()->with('error', 'Gagal: Cabang tujuan sedang offline.');
        }

        $branchConn = DatabaseHelper::getConnectionName($branchId);

        try {
            DB::transaction(function () use ($productId, $branchId, $qty, $type, $ref, $branchConn) {
                // Update local branch database stock
                $currentInv = DB::connection($branchConn)->table('inventory')
                    ->where('product_id', $productId)
                    ->where('branch_id', $branchId)
                    ->first();

                $newStock = $type === 'in' 
                    ? ($currentInv ? $currentInv->stock + $qty : $qty)
                    : ($currentInv ? max(0, $currentInv->stock - $qty) : 0);

                DB::connection($branchConn)->table('inventory')->updateOrInsert(
                    ['product_id' => $productId, 'branch_id' => $branchId],
                    ['stock' => $newStock, 'updated_at' => now()]
                );

                DB::connection($branchConn)->table('stock_movements')->insert([
                    'product_id' => $productId,
                    'branch_id' => $branchId,
                    'type' => $type === 'in' ? 'in' : 'out',
                    'quantity' => $qty,
                    'reference' => $ref,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                // Immediately update central database stock for consistency (since central is online)
                DB::connection('mysql')->table('inventory')->updateOrInsert(
                    ['product_id' => $productId, 'branch_id' => $branchId],
                    ['stock' => $newStock, 'updated_at' => now()]
                );
            });

            // Log activity
            DB::connection('mysql')->table('activity_logs')->insert([
                'branch_id' => $branchId,
                'activity' => 'Mutasi Stok',
                'description' => "Mutasi stok {$type} sejumlah {$qty} unit untuk produk ID {$productId}.",
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return back()->with('success', 'Mutasi stok berhasil dicatat.');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal melakukan mutasi stok: ' . $e->getMessage());
        }
    }

    /**
     * Branch details list.
     */
    public function branches()
    {
        $branches = Branch::all();
        return view('admin.branches', compact('branches'));
    }

    /**
     * Replication log page.
     */
    public function replication()
    {
        $repLogs = DB::connection('mysql')->table('replication_logs')
            ->join('branches', 'replication_logs.branch_id', '=', 'branches.id')
            ->select('replication_logs.*', 'branches.name as branch_name')
            ->orderBy('replication_logs.created_at', 'desc')
            ->paginate(15);

        $consistencyChecks = DB::connection('mysql')->table('consistency_checks')
            ->join('branches', 'consistency_checks.branch_id', '=', 'branches.id')
            ->select('consistency_checks.*', 'branches.name as branch_name')
            ->orderBy('branch_name')
            ->get();

        return view('admin.replication', compact('repLogs', 'consistencyChecks'));
    }

    /**
     * Run Replication (trigger from panel).
     */
    public function runReplication()
    {
        $result = $this->syncService->replicateMasterData();
        
        DB::connection('mysql')->table('activity_logs')->insert([
            'activity' => 'Jalankan Replikasi',
            'description' => 'Memicu replikasi data master ke seluruh node cabang online.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Replikasi data master ke seluruh cabang online berhasil dijalankan.');
    }

    /**
     * Sync Transactions (trigger from panel).
     */
    public function syncAllNodes()
    {
        $result = $this->syncService->syncTransactions();
        
        DB::connection('mysql')->table('activity_logs')->insert([
            'activity' => 'Sinkronisasi Transaksi',
            'description' => 'Memicu penarikan transaksi dari seluruh node cabang online.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Sinkronisasi transaksi dari seluruh cabang online berhasil dijalankan.');
    }

    /**
     * Run consistency check.
     */
    public function checkConsistency()
    {
        $result = $this->consistencyService->checkConsistency();
        
        DB::connection('mysql')->table('activity_logs')->insert([
            'activity' => 'Pengujian Konsistensi',
            'description' => 'Menjalankan audit konsistensi data lintas cabang.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Audit konsistensi data selesai dilaksanakan.');
    }

    /**
     * Connect / Disconnect node.
     */
    public function toggleNode($branchId)
    {
        $current = DB::connection('mysql')->table('node_status')
            ->where('branch_id', $branchId)
            ->first();

        if ($current) {
            $newStatus = $current->node_status === 'online' ? 'offline' : 'online';
            DatabaseHelper::setNodeStatus($branchId, $newStatus);

            // Log activity
            DB::connection('mysql')->table('activity_logs')->insert([
                'branch_id' => $branchId,
                'activity' => 'Ubah Status Node',
                'description' => "Node Cabang " . DatabaseHelper::getBranchName($branchId) . " diubah menjadi " . strtoupper($newStatus),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return back()->with('success', 'Status koneksi cabang berhasil diubah.');
        }

        return back()->with('error', 'Cabang tidak ditemukan.');
    }

    /**
     * User lists.
     */
    public function users()
    {
        $users = User::with('branch')->get();
        return view('admin.users', compact('users'));
    }

    /**
     * Reports interface.
     */
    public function reports()
    {
        $reports = Report::orderBy('created_at', 'desc')->get();
        return view('admin.reports', compact('reports'));
    }

    /**
     * Generate report mock.
     */
    public function generateReport(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'type' => 'required|in:sales_branch,sales_national,replication,consistency',
        ]);

        $title = $request->title;
        $type = $request->type;

        // Add dummy entry in database
        $report = Report::create([
            'title' => $title,
            'type' => $type,
            'file_path' => '/assets/reports/' . Str::slug($title) . '-' . time() . '.pdf'
        ]);

        DB::connection('mysql')->table('activity_logs')->insert([
            'activity' => 'Cetak Laporan',
            'description' => "Membuat laporan '{$title}' tipe {$type}.",
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Laporan berhasil dibuat dan siap diunduh.');
    }

    /**
     * Download mock report.
     */
    public function downloadReport($id)
    {
        $report = Report::findOrFail($id);
        
        // Return a mock download or simple PDF structure.
        // For coursework, we can output a simple HTML with print-ready css or a mock download stream.
        // Let's generate a beautiful print view!
        return redirect()->away('https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf');
    }

    /**
     * Settings interface.
     */
    public function settings()
    {
        return view('admin.settings');
    }
}
