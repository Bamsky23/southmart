<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} - Dokumentasi SouthMart POS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
    <style>
        :root {
            --primary-accent: #0047AB;
            --secondary-accent: #3B82F6;
            --bg-primary: #FFFFFF;
            --text-dark: #1E293B;
            --text-muted: #64748B;
            --border-color: #E2E8F0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #FFFFFF;
            color: var(--text-dark);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .doc-header {
            background-color: #0F172A;
            color: #FFFFFF;
            padding: 1rem 2rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .doc-header h4 {
            margin: 0;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .doc-container {
            display: flex;
            flex-grow: 1;
        }

        .doc-sidebar {
            width: 300px;
            background-color: #F8FAFC;
            border-right: 1px solid var(--border-color);
            padding: 2rem 1.5rem;
            flex-shrink: 0;
        }

        .doc-content {
            flex-grow: 1;
            padding: 3rem 4rem;
            overflow-y: auto;
            max-width: 900px;
        }

        .doc-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .doc-menu-item {
            margin-bottom: 0.5rem;
        }

        .doc-menu-link {
            display: block;
            padding: 0.6rem 1rem;
            color: #475569;
            text-decoration: none;
            border-radius: 6px;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .doc-menu-link:hover {
            background-color: #F1F5F9;
            color: var(--primary-accent);
        }

        .doc-menu-link.active {
            background-color: #EFF6FF;
            color: var(--primary-accent);
            font-weight: 600;
        }

        pre {
            background-color: #0F172A;
            color: #F8F8F2;
            padding: 1.5rem;
            border-radius: 8px;
            font-size: 0.85rem;
            overflow-x: auto;
        }

        code {
            font-family: 'Courier New', Courier, monospace;
        }

        .diagram-box {
            background-color: #F1F5F9;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 1.5rem;
            font-family: monospace;
            white-space: pre;
            overflow-x: auto;
            margin-bottom: 1.5rem;
            color: #334155;
        }
    </style>
</head>
<body>

    <!-- HEADER -->
    <header class="doc-header d-flex justify-content-between align-items-center">
        <h4>
            <i class="bi bi-book-half text-primary"></i> SouthMart POS
        </h4>
        <span class="badge bg-primary px-3 py-2">Modul Akademik Terdistribusi</span>
    </header>

    <!-- CONTAINER -->
    <div class="doc-container">
        
        <!-- SIDEBAR -->
        <aside class="doc-sidebar">
            <h6 class="fw-bold text-muted mb-3 uppercase" style="letter-spacing: 0.05em; font-size: 0.75rem;">TOPIK PERKULIAHAN</h6>
            <ul class="doc-menu">
                @foreach($topics as $key => $titleText)
                <li class="doc-menu-item">
                    <a href="{{ route('doc.show', $key) }}" class="doc-menu-link {{ $activeTopic === $key ? 'active' : '' }}">
                        {{ $titleText }}
                    </a>
                </li>
                @endforeach
            </ul>
        </aside>

        <!-- CONTENT AREA -->
        <main class="doc-content">
            <h2 class="fw-extrabold text-dark mb-4">{{ $title }}</h2>
            
            @switch($activeTopic)
                
                @case('arsitektur-sistem')
                    <p class="lead text-muted mb-4">
                        Desain arsitektur database terdistribusi SouthMart POS didasarkan pada model <strong>Hub-and-Spoke Topology</strong> (Server Pusat dan Cabang).
                    </p>
                    <h5>Karakteristik Arsitektur</h5>
                    <ol class="mb-4">
                        <li><strong>Server Pusat (Central HQ Node)</strong>: Berfungsi sebagai aggregator data transaksi nasional, monitoring status node, penjamin konsistensi data master, dan pembuat laporan analitik gabungan.</li>
                        <li><strong>Branch Nodes (3 Cabang)</strong>: Berfungsi sebagai server lokal di masing-masing supermarket fisik (Tebet, Kemang, Bogor). Menangani proses transaksi POS kasir secara mandiri.</li>
                    </ol>
                    <h5>Manajemen Koneksi Laravel</h5>
                    <p>Aplikasi ini mengonfigurasi 4 koneksi database MySQL terpisah secara nyata pada <code>config/database.php</code>:</p>
                    <pre><code>'connections' => [
    'mysql'           => [ 'database' => 'southmart_central', ... ],
    'node_tebet'      => [ 'database' => 'southmart_tebet', ... ],
    'node_kemang'     => [ 'database' => 'southmart_kemang', ... ],
    'node_bogor'      => [ 'database' => 'southmart_bogor', ... ],
]</code></pre>
                    <p class="mt-3">Dengan konfigurasi ini, aplikasi dapat beralih koneksi secara dinamis bergantung pada akun kasir yang masuk (login) atau tindakan monitoring oleh administrator.</p>
                    @break

                @case('diagram-node')
                    <p class="lead text-muted mb-4">
                        Topologi jaringan fisik dan logis antar node database SouthMart.
                    </p>
                    <div class="diagram-box">
+-----------------------------------------------------------+
|                   CENTRAL DATABASE NODE                   |
|                   (southmart_central)                     |
+-----------------------------------------------------------+
                              |
       +----------------------+----------------------+
       |                      |                      |
       v                      v                      v
+--------------+       +--------------+       +--------------+
|  Tebet Node  |       | Kemang Node  |       |  Bogor Node  |
|  (branch 1)  |       |  (branch 2)  |       |  (branch 3)  |
+--------------+       +--------------+       +--------------+
 (MySQL Shard)          (MySQL Shard)          (MySQL Shard)
                    </div>
                    <h5>Penjelasan Konektivitas</h5>
                    <ul>
                        <li><strong>Master Replication Flow (Top-Down)</strong>: Alur replikasi satu arah dari Pusat (Central) ke seluruh database Cabang untuk tabel master data (Produk, Kategori, User, Cabang).</li>
                        <li><strong>Transaction Sync Flow (Bottom-Up)</strong>: Alur penarikan data transaksi POS yang direkam secara lokal di database Cabang untuk disinkronkan ke tabel gabungan di Server Pusat.</li>
                    </ul>
                    @break

                @case('fragmentasi-horizontal')
                    <p class="lead text-muted mb-4">
                        Teknik pembagian baris tabel transaksi ritel berdasarkan atribut cabang operasional.
                    </p>
                    <h5>Fragmentasi Tabel Terdistribusi</h5>
                    <p>Tabel transaksi (<code>transactions</code>, <code>transaction_details</code>, <code>payments</code>, <code>receipts</code>) di-fragmentasi secara horizontal menggunakan aturan <strong>Range / List Horizontal Fragmentation</strong> berdasarkan kolom <code>branch_id</code>.</p>
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered">
                            <thead>
                                <tr class="table-light">
                                    <th>Node Cabang</th>
                                    <th>Kondisi Fragmentasi</th>
                                    <th>Database Penyimpan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Tebet</td>
                                    <td><code>branch_id = 1</code></td>
                                    <td><code>southmart_tebet</code></td>
                                </tr>
                                <tr>
                                    <td>Kemang</td>
                                    <td><code>branch_id = 2</code></td>
                                    <td><code>southmart_kemang</code></td>
                                </tr>
                                <tr>
                                    <td>Bogor</td>
                                    <td><code>branch_id = 3</code></td>
                                    <td><code>southmart_bogor</code></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <h5>Keuntungan bagi Bisnis</h5>
                    <p>Setiap node cabang hanya menyimpan data transaksi miliknya sendiri. Hal ini mempercepat query lokal POS kasir, menghemat ruang disk lokal di server toko, dan membatasi dampak jika terjadi kebocoran atau kerusakan data di salah satu cabang.</p>
                    @break

                @case('replikasi-data')
                    <p class="lead text-muted mb-4">
                        Mekanisme penyalinan master data global ke seluruh database cabang.
                    </p>
                    <h5>Replikasi Master Data</h5>
                    <p>Untuk memastikan kasir dapat melayani transaksi meskipun koneksi ke pusat terputus (offline), data master berikut wajib direplikasi penuh (Full Replication) ke seluruh database cabang:</p>
                    <ul>
                        <li><code>categories</code> (Kategori Produk)</li>
                        <li><code>products</code> (Katalog Produk & Harga Jual)</li>
                        <li><code>users</code> (Akun autentikasi login Kasir)</li>
                        <li><code>branches</code> (Metadata Cabang)</li>
                    </ul>
                    <h5>Implementasi Kode Replikasi</h5>
                    <p>Fungsi replikasi menggunakan query SQL UPSERT (<code>updateOrInsert</code>) untuk menjamin data cabang selalu mutakhir tanpa ada baris ganda:</p>
                    <pre><code>DB::connection($branchConn)->table('products')->updateOrInsert(
    ['id' => $product->id],
    [
        'barcode' => $product->barcode,
        'sku' => $product->sku,
        'name' => $product->name,
        'category_id' => $product->category_id,
        'buy_price' => $product->buy_price,
        'sell_price' => $product->sell_price,
    ]
);</code></pre>
                    @break

                @case('alur-distribusi-data')
                    <p class="lead text-muted mb-4">
                        Siklus hidup data transaksi dari proses transaksi kasir hingga pencatatan pusat.
                    </p>
                    <h5>Langkah-Langkah Distribusi Data</h5>
                    <div class="diagram-box">
[POS Kasir] ➔ Transaksi disimpan di Database Lokal Cabang
                                ⬇
[Jaringan Online?] ➔ Coba kirim langsung ke Pusat (Central DB)
                    ├── YA ➔ Status: SYNCED (Disalin ke Pusat)
                    └── TIDAK ➔ Status: PENDING SYNC (Antrean Lokal)
                                ⬇
[Sinkronisasi Panel] ➔ Saat admin memicu tombol "Sinkronisasi"
                       Pusat menarik transaksi pending dari Cabang online.
                    </div>
                    <p>Mekanisme ini menjamin operasi POS ritel tetap berjalan 100% lancar walau koneksi internet terputus, memenuhi pilar <strong>Partition Tolerance (P)</strong> dalam teorema CAP.</p>
                    @break

                @case('query-lintas-node')
                    <p class="lead text-muted mb-4">
                        Teknik penggabungan data terdistribusi secara langsung (Live Cross-Node Aggregation).
                    </p>
                    <h5>Konsep Eksekusi</h5>
                    <p>Ketika Administrator mengklik <strong>"Ambil Data Rekapitulasi Nasional"</strong>, aplikasi tidak sekadar membaca tabel pusat, melainkan membuka koneksi MySQL aktif langsung ke 3 database cabang secara paralel, mengeksekusi fungsi SQL agregat lokal, dan menggabungkannya:</p>
                    <pre><code>// Potongan Logika Query Lintas Node di Controller
foreach ($branches as $branch) {
    if (DatabaseHelper::isNodeOnline($branch->id)) {
        $conn = DatabaseHelper::getConnectionName($branch->id);
        
        // Query Agregasi SQL langsung ke database cabang
        $omzet = DB::connection($conn)->table('transactions')->sum('grand_total');
        $txCount = DB::connection($conn)->table('transactions')->count();
        
        $totalOmzet += $omzet;
        $totalTx += $txCount;
    }
}</code></pre>
                    @break

                @case('pengujian-konsistensi')
                    <p class="lead text-muted mb-4">
                        Mekanisme audit data guna memastikan replikasi berjalan sempurna.
                    </p>
                    <h5>Metode Pengujian Konsistensi</h5>
                    <p>Pengujian konsistensi dilakukan dengan membandingkan jumlah baris data (Row Count Comparison) secara silang:</p>
                    <ol>
                        <li>Untuk tabel master (replicated): jumlah baris di database cabang harus tepat sama dengan database pusat.</li>
                        <li>Untuk tabel transaksi (fragmented): jumlah baris di database cabang harus tepat sama dengan jumlah baris di database pusat yang difilter berdasarkan <code>branch_id</code> cabang tersebut.</li>
                    </ol>
                    <p>Rasio konsistensi dihitung menggunakan rumus persentase deviasi jumlah baris:</p>
                    <div class="diagram-box">
Persentase Konsistensi = (Min(Jumlah_Cabang, Jumlah_Pusat) / Max(Jumlah_Cabang, Jumlah_Pusat)) * 100
                    </div>
                    @break

                @case('panduan-instalasi')
                    <p class="lead text-muted mb-4">
                        Petunjuk pemasangan SouthMart POS untuk bahan presentasi perkuliahan.
                    </p>
                    <h5>Prasyarat</h5>
                    <ul>
                        <li>Laragon (menyertakan PHP 8.2/8.3 dan MySQL Server)</li>
                        <li>Composer</li>
                    </ul>
                    <h5>Langkah Instalasi</h5>
                    <ol>
                        <li>Pindahkan/buat direktori proyek ke <code>c:\laragon\www\southmart</code>.</li>
                        <li>Buka terminal/Command Prompt di direktori tersebut.</li>
                        <li>Pastikan database MySQL Laragon menyala.</li>
                        <li>Jalankan perintah penginstal otomatis:
                            <pre><code>php artisan southmart:setup --fresh</code></pre>
                        </li>
                        <li>Perintah di atas akan membuat 4 database secara otomatis di server MySQL Anda dan melakukan migrasi serta seeding secara menyeluruh.</li>
                        <li>Akses web melalui <code>http://localhost:8000</code> setelah menyalakan server laravel:
                            <pre><code>php artisan serve</code></pre>
                        </li>
                    </ol>
                    @break

                @case('panduan-pengujian')
                    <p class="lead text-muted mb-4">
                        Skenario demonstrasi langsung di depan Dosen Pengampu / Penguji.
                    </p>
                    <h5>Skenario 1: Replikasi Real-Time</h5>
                    <ul>
                        <li>Masuk sebagai <strong>Admin Pusat</strong>.</li>
                        <li>Buka menu <strong>Produk</strong>, lalu klik <strong>Tambah Produk Baru</strong>.</li>
                        <li>Setelah disimpan, login ke tab penyamaran (Incognito) sebagai <strong>Kasir Tebet</strong>.</li>
                        <li>Cek pencarian manual di POS, produk baru tersebut akan otomatis terreplikasi dan langsung muncul di sana.</li>
                    </ul>

                    <h5>Skenario 2: Simulasi Offline & Pending Sync Queue</h5>
                    <ul>
                        <li>Pada Dashboard Admin, cari kartu status "SouthMart Tebet" lalu klik <strong>Putuskan Jaringan (Simulasi)</strong>.</li>
                        <li>Login sebagai <strong>Kasir Tebet</strong>, lakukan transaksi POS kasir seperti biasa.</li>
                        <li>Selesai transaksi, perhatikan struk akan berhasil dicetak, namun pada header POS akan muncul badge <strong>"1 Transaksi Pending Sync"</strong>.</li>
                        <li>Kembali ke Dashboard Admin. Periksa statistik omzet nasional, transaksi Tebet yang baru saja dibuat tidak akan masuk ke omzet pusat karena node sedang offline.</li>
                        <li>Di Dashboard Admin, klik <strong>Hubungkan Jaringan (Online)</strong> untuk node Tebet.</li>
                        <li>Di POS Kasir Tebet, klik tombol <strong>Sinkronisasi</strong> (atau tekan <strong>Sinkronisasi Transaksi</strong> di Dashboard Admin).</li>
                        <li>Periksa kembali dashboard pusat, omzet nasional kini bertambah secara real-time dan status konsistensi kembali menjadi 100%.</li>
                    </ul>
                    @break

                @case('dokumentasi-screenshots')
                    <p class="lead text-muted mb-4">
                        Daftar tangkapan layar (screenshots) penting yang wajib disertakan dalam Laporan Tugas Akhir mata kuliah Database Terdistribusi.
                    </p>
                    <h5>Screenshots Rekomendasi</h5>
                    <ol class="lh-lg">
                        <li><strong>Halaman Login Split-Screen</strong>: Menampilkan ilustrasi supermarket, node status bubble, statistik replikasi 99.9%, dan deretan login cepat akun demo.</li>
                        <li><strong>Dashboard Monitoring HQ</strong>: Menampilkan bagan Chart.js penjualan cabang, statistik ringkasan omzet nasional, log sinkronisasi transaksi, dan kartu status koneksi node.</li>
                        <li><strong>POS Interface (Kasir)</strong>: Menampilkan menu kasir yang bersih, barcode scanner input terfokus, tabel keranjang belanja aktif, dan panel kembalian berwarna hijau.</li>
                        <li><strong>Simulasi Putuskan Koneksi</strong>: Screenshot ketika status node diubah menjadi merah (Offline) oleh admin.</li>
                        <li><strong>Struk Thermal Penjualan</strong>: Menampilkan preview cetak struk POS ritel yang rapi.</li>
                        <li><strong>Hasil Query Lintas Node</strong>: Menampilkan rekapitulasi data agregat langsung dari shard-shard cabang online beserta tabel detail shard.</li>
                        <li><strong>Audit Konsistensi</strong>: Menampilkan tabel audit rasio baris data 100% konsisten setelah sinkronisasi selesai dijalankan.</li>
                    </ol>
                    @break

            @endswitch

        </main>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
