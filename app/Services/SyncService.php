<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Helpers\DatabaseHelper;
use App\Models\ReplicationLog;
use App\Models\SynchronizationLog;

class SyncService
{
    /**
     * Run master data replication from Central to all online nodes.
     */
    public function replicateMasterData(): array
    {
        $branches = DB::connection('mysql')->table('branches')->get();
        $tables = ['branches', 'categories', 'products', 'users'];
        $results = [];

        foreach ($branches as $branch) {
            $branchId = $branch->id;
            
            // Check if node is online
            if (!DatabaseHelper::isNodeOnline($branchId)) {
                $results[$branch->name] = [
                    'status' => 'failed',
                    'message' => 'Node sedang offline'
                ];
                
                // Log failed replication
                foreach ($tables as $table) {
                    DB::connection('mysql')->table('replication_logs')->insert([
                        'branch_id' => $branchId,
                        'table_name' => $table,
                        'records_sent' => 0,
                        'records_received' => 0,
                        'status' => 'failed',
                        'error_message' => 'Node offline',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
                continue;
            }

            $branchConn = DatabaseHelper::getConnectionName($branchId);
            $results[$branch->name] = [
                'status' => 'success',
                'tables' => []
            ];

            foreach ($tables as $table) {
                try {
                    // Fetch data from central
                    $centralData = DB::connection('mysql')->table($table)->get();
                    $count = count($centralData);

                    // Insert or update on branch
                    foreach ($centralData as $row) {
                        $rowArray = (array)$row;
                        
                        // Handle user password or special casts
                        DB::connection($branchConn)->table($table)->updateOrInsert(
                            ['id' => $row->id],
                            $rowArray
                        );
                    }

                    // Log successful replication
                    DB::connection('mysql')->table('replication_logs')->insert([
                        'branch_id' => $branchId,
                        'table_name' => $table,
                        'records_sent' => $count,
                        'records_received' => $count,
                        'status' => 'success',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $results[$branch->name]['tables'][$table] = $count;

                } catch (\Exception $e) {
                    // Log failed replication
                    DB::connection('mysql')->table('replication_logs')->insert([
                        'branch_id' => $branchId,
                        'table_name' => $table,
                        'records_sent' => 0,
                        'records_received' => 0,
                        'status' => 'failed',
                        'error_message' => $e->getMessage(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $results[$branch->name]['status'] = 'partial_failed';
                    $results[$branch->name]['tables'][$table] = 'Error: ' . $e->getMessage();
                }
            }
        }

        return $results;
    }

    /**
     * Synchronize transaction data from all online nodes to Central.
     */
    public function syncTransactions(): array
    {
        $branches = DB::connection('mysql')->table('branches')->get();
        $results = [];

        foreach ($branches as $branch) {
            $branchId = $branch->id;

            if (!DatabaseHelper::isNodeOnline($branchId)) {
                $results[$branch->name] = [
                    'status' => 'skipped',
                    'message' => 'Node offline'
                ];
                continue;
            }

            $branchConn = DatabaseHelper::getConnectionName($branchId);

            try {
                // Find all pending transactions on the branch
                $pendingTx = DB::connection($branchConn)->table('transactions')
                    ->where('sync_status', 'pending')
                    ->get();

                $syncedCount = 0;

                foreach ($pendingTx as $tx) {
                    // Start central transaction to ensure atomic sync
                    DB::transaction(function () use ($tx, $branchConn, $branchId, &$syncedCount) {
                        // Get transaction components from node
                        $details = DB::connection($branchConn)->table('transaction_details')
                            ->where('transaction_id', $tx->id)
                            ->get();

                        $payment = DB::connection($branchConn)->table('payments')
                            ->where('transaction_id', $tx->id)
                            ->first();

                        $receipt = DB::connection($branchConn)->table('receipts')
                            ->where('transaction_id', $tx->id)
                            ->first();

                        // 1. Insert into Central Transactions
                        // Check if it already exists by transaction_code (upsert)
                        $existingCentralTx = DB::connection('mysql')->table('transactions')
                            ->where('transaction_code', $tx->transaction_code)
                            ->first();

                        $txData = (array)$tx;
                        $txData['sync_status'] = 'synced'; // Mark synced in Central
                        unset($txData['id']); // Let central auto-generate ID or merge

                        if ($existingCentralTx) {
                            $centralTxId = $existingCentralTx->id;
                            DB::connection('mysql')->table('transactions')
                                ->where('id', $centralTxId)
                                ->update($txData);
                            
                            // Delete old details/payment/receipt in central to overwrite
                            DB::connection('mysql')->table('transaction_details')->where('transaction_id', $centralTxId)->delete();
                            DB::connection('mysql')->table('payments')->where('transaction_id', $centralTxId)->delete();
                            DB::connection('mysql')->table('receipts')->where('transaction_id', $centralTxId)->delete();
                        } else {
                            $centralTxId = DB::connection('mysql')->table('transactions')->insertGetId($txData);
                        }

                        // 2. Insert Details
                        foreach ($details as $detail) {
                            $detailData = (array)$detail;
                            unset($detailData['id']);
                            $detailData['transaction_id'] = $centralTxId;
                            DB::connection('mysql')->table('transaction_details')->insert($detailData);
                        }

                        // 3. Insert Payment
                        if ($payment) {
                            $paymentData = (array)$payment;
                            unset($paymentData['id']);
                            $paymentData['transaction_id'] = $centralTxId;
                            DB::connection('mysql')->table('payments')->insert($paymentData);
                        }

                        // 4. Insert Receipt
                        if ($receipt) {
                            $receiptData = (array)$receipt;
                            unset($receiptData['id']);
                            $receiptData['transaction_id'] = $centralTxId;
                            DB::connection('mysql')->table('receipts')->insert($receiptData);
                        }

                        // 5. Update Status on local node
                        DB::connection($branchConn)->table('transactions')
                            ->where('id', $tx->id)
                            ->update(['sync_status' => 'synced', 'updated_at' => now()]);

                        $syncedCount++;
                    });
                }

                if ($syncedCount > 0) {
                    // Log synchronization success
                    DB::connection('mysql')->table('synchronization_logs')->insert([
                        'branch_id' => $branchId,
                        'action' => 'pull',
                        'records_synced' => $syncedCount,
                        'status' => 'success',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // Update last sync time
                    DB::connection('mysql')->table('node_status')
                        ->where('branch_id', $branchId)
                        ->update(['last_sync' => now(), 'updated_at' => now()]);
                }

                $results[$branch->name] = [
                    'status' => 'success',
                    'synced_records' => $syncedCount
                ];

            } catch (\Exception $e) {
                // Log synchronization failure
                DB::connection('mysql')->table('synchronization_logs')->insert([
                    'branch_id' => $branchId,
                    'action' => 'pull',
                    'records_synced' => 0,
                    'status' => 'failed',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $results[$branch->name] = [
                    'status' => 'failed',
                    'message' => $e->getMessage()
                ];
            }
        }

        return $results;
    }
}
