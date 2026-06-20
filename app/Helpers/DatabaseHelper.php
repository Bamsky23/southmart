<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use App\Models\NodeStatus;

class DatabaseHelper
{
    /**
     * Map branch ID to Laravel database connection name.
     */
    public static function getConnectionName($branchId): string
    {
        switch ((int)$branchId) {
            case 1: return 'node_tebet';
            case 2: return 'node_kemang';
            case 3: return 'node_bogor';
            default: return 'mysql'; // Central
        }
    }

    /**
     * Check if a branch node is simulated as online.
     */
    public static function isNodeOnline($branchId): bool
    {
        // Central is always online
        if (empty($branchId) || $branchId === 'central') {
            return true;
        }

        try {
            // Read from Central database
            $status = DB::connection('mysql')->table('node_status')
                ->where('branch_id', $branchId)
                ->first();

            return $status ? ($status->node_status === 'online') : true;
        } catch (\Exception $e) {
            // Fallback if central db itself is having issues
            return true;
        }
    }

    /**
     * Run a database operation on a specific branch node.
     * Throws an exception if the node is simulated as offline.
     */
    public static function runOnNode($branchId, callable $callback)
    {
        $connection = self::getConnectionName($branchId);

        if (!self::isNodeOnline($branchId)) {
            throw new \Exception("Koneksi gagal: Node Cabang " . self::getBranchName($branchId) . " sedang OFFLINE.");
        }

        try {
            return $callback(DB::connection($connection));
        } catch (\Exception $e) {
            throw new \Exception("Kesalahan Database pada Node " . self::getBranchName($branchId) . ": " . $e->getMessage());
        }
    }

    /**
     * Toggle the status of a branch node.
     */
    public static function setNodeStatus($branchId, string $status): void
    {
        DB::connection('mysql')->table('node_status')
            ->where('branch_id', $branchId)
            ->update([
                'node_status' => $status,
                'db_status' => $status,
                'updated_at' => now(),
            ]);
    }

    /**
     * Helper to get human readable branch name.
     */
    public static function getBranchName($branchId): string
    {
        switch ((int)$branchId) {
            case 1: return 'Tebet';
            case 2: return 'Kemang';
            case 3: return 'Bogor';
            default: return 'Pusat (HQ)';
        }
    }
}
