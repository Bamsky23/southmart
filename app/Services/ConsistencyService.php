<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Helpers\DatabaseHelper;
use App\Models\ConsistencyCheck;

class ConsistencyService
{
    /**
     * Run consistency check on all nodes and master/fragmented tables.
     */
    public function checkConsistency(): array
    {
        $branches = DB::connection('mysql')->table('branches')->get();
        $masterTables = ['branches', 'categories', 'products', 'users'];
        $fragmentedTables = ['transactions', 'inventory', 'stock_movements'];
        $results = [];

        // Clean old consistency checks for a fresh run
        DB::connection('mysql')->table('consistency_checks')->truncate();

        foreach ($branches as $branch) {
            $branchId = $branch->id;
            
            // Skip offline nodes
            if (!DatabaseHelper::isNodeOnline($branchId)) {
                $results[$branch->name] = [
                    'status' => 'offline',
                    'message' => 'Node offline, tidak bisa melakukan pengecekan'
                ];
                continue;
            }

            $branchConn = DatabaseHelper::getConnectionName($branchId);
            $results[$branch->name] = [
                'status' => 'online',
                'tables' => []
            ];

            // 1. Check Master Tables (Replicated)
            foreach ($masterTables as $table) {
                try {
                    $branchCount = DB::connection($branchConn)->table($table)->count();
                    $centralCount = DB::connection('mysql')->table($table)->count();

                    $isConsistent = ($branchCount === $centralCount);
                    $percentage = 100.00;
                    if ($centralCount > 0 || $branchCount > 0) {
                        $max = max($branchCount, $centralCount);
                        $min = min($branchCount, $centralCount);
                        $percentage = ($min / $max) * 100;
                    }

                    DB::connection('mysql')->table('consistency_checks')->insert([
                        'branch_id' => $branchId,
                        'table_name' => $table,
                        'branch_count' => $branchCount,
                        'central_count' => $centralCount,
                        'is_consistent' => $isConsistent,
                        'percentage' => $percentage,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $results[$branch->name]['tables'][$table] = [
                        'branch' => $branchCount,
                        'central' => $centralCount,
                        'percentage' => $percentage,
                        'status' => $isConsistent ? 'Consistent' : 'Inconsistent'
                    ];
                } catch (\Exception $e) {
                    $results[$branch->name]['tables'][$table] = 'Error: ' . $e->getMessage();
                }
            }

            // 2. Check Fragmented Tables (Branch Specific)
            foreach ($fragmentedTables as $table) {
                try {
                    // Branch count (contains only its own data)
                    $branchCount = DB::connection($branchConn)->table($table)->count();
                    
                    // Central count (filtered by branch_id to check this branch's fragment consistency)
                    $centralCount = DB::connection('mysql')->table($table)
                        ->where('branch_id', $branchId)
                        ->count();

                    $isConsistent = ($branchCount === $centralCount);
                    $percentage = 100.00;
                    if ($centralCount > 0 || $branchCount > 0) {
                        $max = max($branchCount, $centralCount);
                        $min = min($branchCount, $centralCount);
                        $percentage = ($min / $max) * 100;
                    }

                    DB::connection('mysql')->table('consistency_checks')->insert([
                        'branch_id' => $branchId,
                        'table_name' => $table,
                        'branch_count' => $branchCount,
                        'central_count' => $centralCount,
                        'is_consistent' => $isConsistent,
                        'percentage' => $percentage,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $results[$branch->name]['tables'][$table] = [
                        'branch' => $branchCount,
                        'central' => $centralCount,
                        'percentage' => $percentage,
                        'status' => $isConsistent ? 'Consistent' : 'Inconsistent'
                    ];
                } catch (\Exception $e) {
                    $results[$branch->name]['tables'][$table] = 'Error: ' . $e->getMessage();
                }
            }
        }

        return $results;
    }
}
