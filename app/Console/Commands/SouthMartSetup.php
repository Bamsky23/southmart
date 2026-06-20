<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SouthMartSetup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'southmart:setup {--fresh : Recreate databases and run fresh migrations}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup SouthMart POS and Distributed Databases (Central and 5 Nodes)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting SouthMart Distributed Database Setup...');

        // 1. Create databases
        $databases = [
            'southmart_central',
            'southmart_tebet',
            'southmart_kemang',
            'southmart_bogor',
        ];

        // Temp connection config to MySQL without specifying a database
        $config = config('database.connections.mysql');
        $config['database'] = null; // Connect to server only
        config(['database.connections.temp_setup' => $config]);

        try {
            foreach ($databases as $db) {
                if ($this->option('fresh')) {
                    $this->info("Dropping database (if exists): {$db}...");
                    DB::connection('temp_setup')->statement("DROP DATABASE IF EXISTS {$db}");
                }
                $this->info("Creating database (if not exists): {$db}...");
                DB::connection('temp_setup')->statement("CREATE DATABASE IF NOT EXISTS {$db} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            }
        } catch (\Exception $e) {
            $this->error("Failed to create databases: " . $e->getMessage());
            $this->error("Please make sure MySQL is running and root credentials in .env are correct.");
            return 1;
        }

        // 2. Run migrations on all databases
        $connections = [
            'mysql' => 'southmart_central',
            'node_tebet' => 'southmart_tebet',
            'node_kemang' => 'southmart_kemang',
            'node_bogor' => 'southmart_bogor',
        ];

        foreach ($connections as $conn => $db) {
            $this->info("Running migrations on database: {$db} (connection: {$conn})...");
            try {
                Artisan::call('migrate:fresh', [
                    '--database' => $conn,
                    '--path' => 'database/migrations',
                    '--force' => true,
                ]);
                $this->line(Artisan::output());
            } catch (\Exception $e) {
                $this->error("Migration failed on connection {$conn} ({$db}): " . $e->getMessage());
                return 1;
            }
        }

        // 3. Seed data
        $this->info('Seeding master data and historical transactions...');
        try {
            $this->seedAll($connections);
        } catch (\Exception $e) {
            $this->error("Seeding failed: " . $e->getMessage());
            return 1;
        }

        $this->info('SouthMart Distributed Database Setup Completed Successfully!');
        return 0;
    }

    private function seedAll(array $connections)
    {
        // Define Seed Data
        $branches = [
            ['id' => 1, 'name' => 'SouthMart Tebet', 'code' => 'TEBET', 'location' => 'Jakarta Selatan', 'ip_address' => '192.168.1.10'],
            ['id' => 2, 'name' => 'SouthMart Kemang', 'code' => 'KEMANG', 'location' => 'Jakarta Selatan', 'ip_address' => '192.168.1.11'],
            ['id' => 3, 'name' => 'SouthMart Bogor', 'code' => 'BOGOR', 'location' => 'Jawa Barat', 'ip_address' => '192.168.1.12'],
        ];

        $categories = [
            ['id' => 1, 'name' => 'Makanan', 'slug' => 'makanan'],
            ['id' => 2, 'name' => 'Minuman', 'slug' => 'minuman'],
            ['id' => 3, 'name' => 'Snack', 'slug' => 'snack'],
            ['id' => 4, 'name' => 'Perawatan Diri', 'slug' => 'perawatan-diri'],
            ['id' => 5, 'name' => 'Rumah Tangga', 'slug' => 'rumah-tangga'],
            ['id' => 6, 'name' => 'Obat-obatan', 'slug' => 'obat-obatan'],
            ['id' => 7, 'name' => 'Elektronik', 'slug' => 'elektronik'],
            ['id' => 8, 'name' => 'Hewan Peliharaan', 'slug' => 'Hewan Peliharaan'],
            ['id' => 9, 'name' => 'Sembako', 'slug' => 'sembako'],
            ['id' => 10, 'name' => 'Bayi & Anak', 'slug' => 'bayi-anak'],
            ['id' => 11, 'name' => 'Frozen Food', 'slug' => 'frozen-food'],
        ];

        $jsonPath = 'C:\\Users\\WELCOME\\.gemini\\antigravity-ide\\brain\\e75b0e9c-c5b5-42da-8282-2678654e38b1\\scratch\\scraped_products.json';
        if (file_exists($jsonPath)) {
            $products = json_decode(file_get_contents($jsonPath), true);
        } else {
            $products = [];
        }

        $users = [
            ['id' => 1, 'name' => 'Admin Pusat', 'email' => 'admin@southmart.id', 'password' => Hash::make('password'), 'role' => 'admin', 'branch_id' => null],
            ['id' => 2, 'name' => 'Kasir Tebet', 'email' => 'tebet@southmart.id', 'password' => Hash::make('password'), 'role' => 'kasir', 'branch_id' => 1],
            ['id' => 3, 'name' => 'Kasir Kemang', 'email' => 'kemang@southmart.id', 'password' => Hash::make('password'), 'role' => 'kasir', 'branch_id' => 2],
            ['id' => 4, 'name' => 'Kasir Bogor', 'email' => 'bogor@southmart.id', 'password' => Hash::make('password'), 'role' => 'kasir', 'branch_id' => 3],
        ];

        // Seed to ALL databases (Master data replication)
        foreach ($connections as $conn => $dbName) {
            $this->info("Replicating master tables to {$dbName}...");

            // Branches
            foreach ($branches as $branch) {
                DB::connection($conn)->table('branches')->updateOrInsert(['id' => $branch['id']], $branch);
            }

            // Categories
            foreach ($categories as $cat) {
                DB::connection($conn)->table('categories')->updateOrInsert(['id' => $cat['id']], $cat);
            }

            // Products
            foreach ($products as $prod) {
                DB::connection($conn)->table('products')->updateOrInsert(['id' => $prod['id']], $prod);
            }

            // Users
            foreach ($users as $user) {
                DB::connection($conn)->table('users')->updateOrInsert(['id' => $user['id']], $user);
            }
        }

        // Seed local inventories for each branch connection (excluding mysql - central has inventory too, let's seed all)
        foreach ($connections as $conn => $dbName) {
            $isCentral = ($conn === 'mysql');
            $branchId = null;

            if (!$isCentral) {
                // Determine branch_id from connection name
                if (str_contains($conn, 'tebet')) $branchId = 1;
                elseif (str_contains($conn, 'kemang')) $branchId = 2;
                elseif (str_contains($conn, 'bogor')) $branchId = 3;
            }

            // Central database gets stock seeded for all branches
            if ($isCentral) {
                for ($b = 1; $b <= 3; $b++) {
                    foreach ($products as $prod) {
                        DB::connection($conn)->table('inventory')->updateOrInsert(
                            ['product_id' => $prod['id'], 'branch_id' => $b],
                            ['stock' => rand(30, 80), 'minimum_stock' => 10, 'created_at' => now(), 'updated_at' => now()]
                        );
                    }
                }
            } else {
                // Branch nodes only get stock seeded for themselves
                foreach ($products as $prod) {
                    DB::connection($conn)->table('inventory')->updateOrInsert(
                        ['product_id' => $prod['id'], 'branch_id' => $branchId],
                        ['stock' => rand(35, 75), 'minimum_stock' => 10, 'created_at' => now(), 'updated_at' => now()]
                    );
                }
            }
        }

        // Seed connection statuses and log data on central database only
        $this->info("Initializing connection and replication logs in Central HQ...");
        for ($b = 1; $b <= 3; $b++) {
            // Node status
            DB::connection('mysql')->table('node_status')->updateOrInsert(
                ['branch_id' => $b],
                [
                    'node_status' => 'online',
                    'db_status' => 'online',
                    'last_sync' => now()->subMinutes(rand(1, 15)),
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );

            // Replication logs
            $masterTables = ['branches', 'categories', 'products', 'users'];
            foreach ($masterTables as $table) {
                DB::connection('mysql')->table('replication_logs')->insert([
                    'branch_id' => $b,
                    'table_name' => $table,
                    'records_sent' => 10,
                    'records_received' => 10,
                    'status' => 'success',
                    'created_at' => now()->subHours(1),
                    'updated_at' => now()->subHours(1)
                ]);
            }

            // Sync logs
            DB::connection('mysql')->table('synchronization_logs')->insert([
                'branch_id' => $b,
                'action' => 'pull',
                'records_synced' => rand(5, 20),
                'status' => 'success',
                'created_at' => now()->subHours(1),
                'updated_at' => now()->subHours(1)
            ]);

            // Consistency checks
            foreach ($masterTables as $table) {
                $count = DB::connection('mysql')->table($table)->count();
                DB::connection('mysql')->table('consistency_checks')->insert([
                    'branch_id' => $b,
                    'table_name' => $table,
                    'branch_count' => $count,
                    'central_count' => $count,
                    'is_consistent' => true,
                    'percentage' => 100.00,
                    'created_at' => now()->subMinutes(30),
                    'updated_at' => now()->subMinutes(30)
                ]);
            }
        }

        // Seed historical transactions
        // We will seed historical transactions on each branch database, and also replicate them to central to simulate a previously synchronized state.
        // Let's create transactions for the last 5 days
        $methods = ['tunai', 'qris', 'debit', 'kredit', 'transfer'];
        
        foreach ($connections as $conn => $dbName) {
            $isCentral = ($conn === 'mysql');
            if ($isCentral) continue; // We will seed branch nodes first, then merge them into central.
            
            $branchId = null;
            $branchCode = '';
            if (str_contains($conn, 'tebet')) { $branchId = 1; $branchCode = 'TBT'; }
            elseif (str_contains($conn, 'kemang')) { $branchId = 2; $branchCode = 'KMG'; }
            elseif (str_contains($conn, 'bogor')) { $branchId = 3; $branchCode = 'BGR'; }

            $cashierId = $branchId + 1; // User IDs: Tebet is 2, Kemang 3, etc.
            
            $this->info("Seeding transactions for branch node: {$branchCode}...");

            // Seed only 1 transaction for Tebet & Kemang, 0 for Bogor (total 2 transactions across all nodes)
            $txLimit = ($branchCode === 'BGR') ? 0 : 1;
            for ($i = 1; $i <= $txLimit; $i++) {
                $date = now()->subDays(rand(0, 7))->subHours(rand(0, 10))->subMinutes(rand(0, 50));
                $txCode = "TX-{$branchCode}-" . $date->format('YmdHis') . "-" . str_pad($i, 4, '0', STR_PAD_LEFT);
                
                // Pick random products
                $txProducts = count($products) > 5 ? array_slice($products, rand(0, count($products) - 5), rand(1, 4)) : $products;
                $total = 0;
                $txDetails = [];

                foreach ($txProducts as $prod) {
                    $qty = rand(1, 3);
                    $subtotal = $prod['sell_price'] * $qty;
                    $total += $subtotal;
                    
                    $txDetails[] = [
                        'product_id' => $prod['id'],
                        'quantity' => $qty,
                        'price' => $prod['sell_price'],
                        'subtotal' => $subtotal,
                        'created_at' => $date,
                        'updated_at' => $date
                    ];
                }

                $tax = round($total * 0.11); // 11% PPN
                $grandTotal = $total + $tax;

                // Insert into transactions
                $txId = DB::connection($conn)->table('transactions')->insertGetId([
                    'transaction_code' => $txCode,
                    'branch_id' => $branchId,
                    'user_id' => $cashierId,
                    'total_price' => $total,
                    'discount' => 0,
                    'tax' => $tax,
                    'grand_total' => $grandTotal,
                    'payment_status' => 'completed',
                    'sync_status' => 'synced', // historical transactions are already synced
                    'created_at' => $date,
                    'updated_at' => $date
                ]);

                // Insert details
                foreach ($txDetails as $detail) {
                    $detail['transaction_id'] = $txId;
                    DB::connection($conn)->table('transaction_details')->insert($detail);
                }

                // Insert payment
                $method = $methods[array_rand($methods)];
                $amountPaid = ceil($grandTotal / 5000) * 5000;
                if ($method !== 'tunai') $amountPaid = $grandTotal; // QRIS/Card exact amount
                $change = $amountPaid - $grandTotal;

                DB::connection($conn)->table('payments')->insert([
                    'transaction_id' => $txId,
                    'method' => $method,
                    'amount_paid' => $amountPaid,
                    'amount_change' => $change,
                    'created_at' => $date,
                    'updated_at' => $date
                ]);

                // Insert receipt
                DB::connection($conn)->table('receipts')->insert([
                    'transaction_id' => $txId,
                    'receipt_code' => 'REC-' . $txCode,
                    'printed_at' => $date,
                    'created_at' => $date,
                    'updated_at' => $date
                ]);

                // Central aggregation copy
                // For demonstrating central monitoring, we insert these records into the central database.
                $centralTxId = DB::connection('mysql')->table('transactions')->insertGetId([
                    'transaction_code' => $txCode,
                    'branch_id' => $branchId,
                    'user_id' => $cashierId,
                    'total_price' => $total,
                    'discount' => 0,
                    'tax' => $tax,
                    'grand_total' => $grandTotal,
                    'payment_status' => 'completed',
                    'sync_status' => 'synced',
                    'created_at' => $date,
                    'updated_at' => $date
                ]);

                foreach ($txDetails as $detail) {
                    $detail['transaction_id'] = $centralTxId;
                    DB::connection('mysql')->table('transaction_details')->insert($detail);
                }

                DB::connection('mysql')->table('payments')->insert([
                    'transaction_id' => $centralTxId,
                    'method' => $method,
                    'amount_paid' => $amountPaid,
                    'amount_change' => $change,
                    'created_at' => $date,
                    'updated_at' => $date
                ]);

                DB::connection('mysql')->table('receipts')->insert([
                    'transaction_id' => $centralTxId,
                    'receipt_code' => 'REC-' . $txCode,
                    'printed_at' => $date,
                    'created_at' => $date,
                    'updated_at' => $date
                ]);
            }
        }
    }
}
