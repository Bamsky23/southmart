<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS {{ $branchName }} - SouthMart</title>
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
            background-color: #F8FAFC;
            color: var(--text-dark);
            height: 100vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* Header POS */
        .pos-header {
            background-color: #FFFFFF;
            border-bottom: 1px solid var(--border-color);
            padding: 0.8rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-shrink: 0;
            z-index: 10;
        }

        .pos-header img {
            max-height: 40px;
            object-fit: contain;
        }

        /* Main Workspace split screen */
        .pos-workspace {
            display: flex;
            flex-grow: 1;
            overflow: hidden;
        }

        /* Left side: Product Catalog (65%) */
        .catalog-pane {
            width: 65%;
            background-color: #F8FAFC;
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            padding: 1.2rem;
            overflow: hidden;
        }

        /* Right side: Cart & Checkout (35%) */
        .checkout-pane {
            width: 35%;
            background-color: #FFFFFF;
            display: flex;
            flex-direction: column;
            padding: 0.9rem;
            overflow: hidden;
        }

        /* Search & Filter styles */
        .search-box {
            position: relative;
            margin-bottom: 1rem;
        }

        .search-box input {
            padding: 0.75rem 1rem 0.75rem 2.5rem;
            border-radius: 10px;
            border: 1px solid var(--border-color);
            background-color: #FFFFFF;
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }

        .search-box input:focus {
            border-color: var(--secondary-accent);
            box-shadow: 0 0 0 0.25rem rgba(59, 130, 246, 0.1);
        }

        .search-box i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
        }

        /* Horizontal scrolling categories */
        .category-scroll {
            display: flex;
            gap: 8px;
            overflow-x: auto;
            padding-bottom: 0.8rem;
            margin-bottom: 1rem;
            flex-shrink: 0;
            scrollbar-width: thin;
        }

        .category-tab {
            background-color: #FFFFFF;
            border: 1px solid var(--border-color);
            border-radius: 30px;
            padding: 0.5rem 1.2rem;
            font-size: 0.85rem;
            font-weight: 600;
            color: #475569;
            white-space: nowrap;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .category-tab.active {
            background-color: var(--primary-accent);
            border-color: var(--primary-accent);
            color: #FFFFFF;
        }

        /* Product Cards Grid */
        .product-grid {
            flex-grow: 1;
            overflow-y: auto;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 12px;
            padding-bottom: 1rem;
        }

        .product-card {
            background-color: #FFFFFF;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 1rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
        }

        .product-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
            border-color: var(--secondary-accent);
        }

        .category-badge-strip {
            height: 4px;
            width: 40px;
            border-radius: 20px;
            margin-bottom: 0.75rem;
        }

        .product-card-title {
            font-size: 0.9rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 0.25rem;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            height: 2.8em;
        }

        .product-card-meta {
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-bottom: 0.5rem;
        }

        .product-card-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: auto;
        }

        .product-card-price {
            font-size: 0.95rem;
            font-weight: 800;
            color: var(--primary-accent);
        }

        /* Cart and payment UI */
        .cart-section {
            flex-grow: 1;
            flex-shrink: 1;
            min-height: 180px;
            overflow-y: auto;
            border: 1px solid var(--border-color);
            border-radius: 10px;
            margin-bottom: 0.75rem;
        }

        .grand-total-display {
            background-color: #EFF6FF;
            border: 1px solid rgba(59, 130, 246, 0.15);
            border-radius: 10px;
            padding: 0.6rem 0.9rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.75rem;
            flex-shrink: 0;
        }

        .payment-method-btn {
            background-color: #FFFFFF;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 0.4rem 0.6rem;
            font-weight: 600;
            font-size: 0.8rem;
            color: var(--text-dark);
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .payment-method-btn.active {
            background-color: var(--primary-accent);
            border-color: var(--primary-accent);
            color: #FFFFFF;
        }

        .change-display {
            font-size: 1.25rem;
            font-weight: 800;
            color: #10B981;
        }

        .btn-checkout {
            background-color: var(--primary-accent);
            border: none;
            color: #FFFFFF;
            padding: 0.7rem;
            border-radius: 10px;
            font-weight: 700;
            font-size: 1rem;
            transition: all 0.3s ease;
            width: 100%;
        }

        .btn-checkout:hover {
            background-color: #003682;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,71,171,0.25);
        }

        .indicator {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
        }

        .indicator.online {
            background-color: #10B981;
            box-shadow: 0 0 8px #10B981;
        }

        .indicator.offline {
            background-color: #EF4444;
            box-shadow: 0 0 8px #EF4444;
        }
    </style>
</head>
<body>

    <!-- HEADER -->
    <header class="pos-header">
        <div class="d-flex align-items-center gap-3">
            <img src="/assets/images/logo.png" alt="SouthMart Logo">
            <h4 class="fw-bold m-0 text-dark">SouthMart - {{ $branchName }}</h4>
            
            @if($isOnline)
                <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1.5 rounded-pill d-flex align-items-center gap-1.5">
                    <span class="indicator online"></span> Hubungan Pusat: Online
                </span>
            @else
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1.5 rounded-pill d-flex align-items-center gap-1.5">
                    <span class="indicator offline"></span> Hubungan Pusat: Offline
                </span>
            @endif
        </div>

        <div class="d-flex align-items-center gap-2">
            @if($pendingCount > 0)
                <span class="badge bg-warning text-dark px-3 py-2 fw-semibold">
                    <i class="bi bi-clock-history me-1"></i> {{ $pendingCount }} Transaksi Pending Sync
                </span>
                <form action="{{ route('kasir.sync-local') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-warning btn-sm fw-bold">
                        <i class="bi bi-arrow-repeat me-1"></i> Sinkronisasi
                    </button>
                </form>
            @endif

            <a href="{{ route('kasir.history') }}" class="btn btn-outline-secondary btn-sm fw-semibold">
                <i class="bi bi-clock-history me-1"></i> Riwayat Penjualan
            </a>

            <form action="{{ route('kasir.void-latest') }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan transaksi terakhir? Tindakan ini akan menghapus transaksi dan mengembalikan stok barang.')">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-sm fw-semibold">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Undo Transaksi Terakhir
                </button>
            </form>

            <div class="border-start ms-2 ps-3 d-flex align-items-center gap-2">
                <span class="small fw-semibold text-dark">{{ Auth::user()->name }}</span>
                <a href="{{ route('logout') }}" class="btn btn-sm btn-outline-danger" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="bi bi-box-arrow-right"></i> Keluar
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            </div>
        </div>
    </header>

    <!-- WORKSPACE -->
    <div class="pos-workspace">
        
        <!-- LEFT PANEL: PRODUCT CATALOG (65%) -->
        <div class="catalog-pane">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-2 py-2 px-3 fw-medium" role="alert" style="font-size: 0.85rem;">
                    <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show mb-2 py-2 px-3 fw-medium" role="alert" style="font-size: 0.85rem;">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                    <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Search bar -->
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" id="catalog-search" class="form-control w-100" placeholder="Cari produk berdasarkan nama, SKU, atau barcode...">
            </div>

            <!-- Category Filter scroll -->
            <div class="category-scroll">
                <div class="category-tab active" data-category="all">Semua Kategori</div>
                @foreach($categories as $cat)
                <div class="category-tab" data-category="{{ $cat->id }}">{{ $cat->name }}</div>
                @endforeach
            </div>

            <!-- Product Grid -->
            <div class="product-grid" id="product-grid-container">
                @foreach($products as $prod)
                @php
                    // Set category color strip
                    $stripColor = '#94a3b8'; // Default grey
                    if ($prod->category_id == 1) $stripColor = '#10B981'; // Emerald Green (Makanan)
                    elseif ($prod->category_id == 2) $stripColor = '#3B82F6'; // Blue (Minuman)
                    elseif ($prod->category_id == 3) $stripColor = '#F59E0B'; // Amber (Snack)
                    elseif ($prod->category_id == 4) $stripColor = '#EC4899'; // Pink (Perawatan Diri)
                    elseif ($prod->category_id == 5) $stripColor = '#6B7280'; // Grey (Rumah Tangga)
                    elseif ($prod->category_id == 6) $stripColor = '#EF4444'; // Red (Obat-obatan)
                    elseif ($prod->category_id == 7) $stripColor = '#8B5CF6'; // Purple (Elektronik)
                    elseif ($prod->category_id == 8) $stripColor = '#06B6D4'; // Cyan (Hewan Peliharaan)
                    elseif ($prod->category_id == 9) $stripColor = '#84CC16'; // Lime Green (Sembako)
                    elseif ($prod->category_id == 10) $stripColor = '#F97316'; // Orange (Bayi & Anak)
                    elseif ($prod->category_id == 11) $stripColor = '#14B8A6'; // Teal (Frozen Food)

                    // Stock badge properties
                    $stockBadgeClass = 'bg-success-subtle text-success border-success-subtle';
                    $stockBadgeText = "Stok: {$prod->stock}";
                    if ($prod->stock == 0) {
                        $stockBadgeClass = 'bg-danger-subtle text-danger border-danger-subtle';
                        $stockBadgeText = "Stok Habis";
                    } elseif ($prod->stock <= 10) {
                        $stockBadgeClass = 'bg-warning-subtle text-warning border-warning-subtle';
                        $stockBadgeText = "Stok Menipis: {$prod->stock}";
                    }
                @endphp
                <div class="product-card" 
                     data-id="{{ $prod->id }}" 
                     data-barcode="{{ $prod->barcode }}"
                     data-sku="{{ $prod->sku }}"
                     data-category="{{ $prod->category_id }}"
                     data-name="{{ $prod->name }}"
                     data-price="{{ $prod->sell_price }}"
                     data-stock="{{ $prod->stock }}">
                    
                    <div style="height: 100px; display: flex; align-items: center; justify-content: center; background-color: #F8FAFC; border-radius: 8px; margin-bottom: 0.5rem; overflow: hidden; border: 1px solid #F1F5F9;">
                        <img src="{{ $prod->image_url ?? 'https://images.unsplash.com/photo-1612927601601-6638404737ce?w=200' }}" alt="{{ $prod->name }}" style="max-height: 100%; max-width: 100%; object-fit: contain; transition: transform 0.2s;" class="product-img" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1542838132-92c53300491e?w=200';">
                    </div>
                    
                    <div>
                        <!-- Category Accent strip -->
                        <div class="category-badge-strip" style="background-color: {{ $stripColor }};"></div>
                        <h6 class="product-card-title">{{ $prod->name }}</h6>
                        <div class="product-card-meta">SKU: {{ $prod->sku }}</div>
                    </div>

                    <div class="product-card-footer">
                        <span class="product-card-price">Rp {{ number_format($prod->sell_price, 0, ',', '.') }}</span>
                        <span class="badge {{ $stockBadgeClass }} border rounded-pill" style="font-size: 0.7rem;">{{ $stockBadgeText }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- RIGHT PANEL: CART & CHECKOUT (35%) -->
        <div class="checkout-pane">
            <h5 class="fw-bold mb-3 text-dark"><i class="bi bi-cart3 me-2 text-primary"></i>Keranjang Belanja</h5>

            <!-- Cart Section -->
            <div class="cart-section">
                <table class="table table-hover align-middle m-0" id="cart-table" style="font-size: 0.85rem;">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th>Nama Produk</th>
                            <th class="text-center" style="width: 80px;">Qty</th>
                            <th class="text-end" style="width: 100px;">Total</th>
                            <th class="text-center" style="width: 40px;"></th>
                        </tr>
                    </thead>
                    <tbody id="cart-tbody">
                        <!-- Items generated here -->
                        <tr>
                            <td colspan="4" class="text-center text-muted py-5">Keranjang kosong. Klik produk di sebelah kiri untuk menambah.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Cart Summary -->
            <div class="border-top pt-2 mb-2 fs-7" style="font-size: 0.8rem;">
                <div class="d-flex justify-content-between mb-1 text-muted">
                    <span>Subtotal:</span>
                    <span id="cart-subtotal">Rp 0</span>
                </div>
                <div class="d-flex justify-content-between mb-0 text-muted">
                    <span>Pajak (PPN 11%):</span>
                    <span id="cart-tax">Rp 0</span>
                </div>
            </div>

            <!-- Grand Total displays -->
            <div class="grand-total-display">
                <span class="text-muted small fw-bold font-monospace">TOTAL BELANJA</span>
                <h3 class="fw-extrabold text-primary m-0" id="grand-total-text" style="font-size: 1.4rem;">Rp 0</h3>
            </div>

            <!-- Payment Methods -->
            <div class="mb-2">
                <label class="form-label fw-bold small text-muted mb-1">METODE PEMBAYARAN</label>
                <div class="d-flex gap-1.5 flex-wrap">
                    <div class="payment-method-btn flex-fill active text-center" data-method="tunai">Tunai</div>
                    <div class="payment-method-btn flex-fill text-center" data-method="qris">QRIS</div>
                    <div class="payment-method-btn flex-fill text-center" data-method="debit">Debit</div>
                    <div class="payment-method-btn flex-fill text-center" data-method="kredit">Kredit</div>
                    <div class="payment-method-btn flex-fill text-center" data-method="transfer">Transfer</div>
                </div>
            </div>

            <!-- Payment input -->
            <div class="mb-2" id="cash-input-section">
                <label for="amount-paid-input" class="form-label fw-bold small text-muted mb-1">NOMINAL UANG BAYAR (RP)</label>
                <input type="number" id="amount-paid-input" class="form-control fw-bold" placeholder="Nominal..." style="font-size: 1.1rem; padding: 0.35rem 0.6rem;">
                
                <!-- Quick cash suggestions -->
                <div class="d-flex flex-wrap gap-1 mt-1">
                    <button class="btn btn-outline-secondary btn-sm quick-cash-btn py-0.5 px-2" data-cash="10000" style="font-size: 0.72rem;">10rb</button>
                    <button class="btn btn-outline-secondary btn-sm quick-cash-btn py-0.5 px-2" data-cash="20000" style="font-size: 0.72rem;">20rb</button>
                    <button class="btn btn-outline-secondary btn-sm quick-cash-btn py-0.5 px-2" data-cash="50000" style="font-size: 0.72rem;">50rb</button>
                    <button class="btn btn-outline-secondary btn-sm quick-cash-btn py-0.5 px-2" data-cash="100000" style="font-size: 0.72rem;">100rb</button>
                    <button class="btn btn-outline-secondary btn-sm py-0.5 px-2" id="btn-exact-cash" style="font-size: 0.72rem;">Pas</button>
                    <button class="btn btn-outline-danger btn-sm py-0.5 px-2" id="btn-clear-cash" style="font-size: 0.72rem;"><i class="bi bi-arrow-counterclockwise"></i> Reset</button>
                </div>
            </div>

            <!-- Change calculations -->
            <div class="mb-2 d-flex justify-content-between align-items-center" id="change-section">
                <span class="text-muted fw-bold small">KEMBALIAN:</span>
                <div class="change-display m-0" id="change-display">Rp 0</div>
            </div>

            <!-- Action Checkout button -->
            <div class="d-grid mt-2">
                <button class="btn-checkout" id="btn-process-checkout">
                    PROSES TRANSAKSI
                </button>
            </div>
        </div>

    </div>

    <!-- Bootstrap Modal for Non-Cash Payment Interaction -->
    <div class="modal fade" id="nonCashPaymentModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="nonCashPaymentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg border-0" style="border-radius: 16px;">
                <div class="modal-header border-0 bg-primary text-white" style="border-top-left-radius: 16px; border-top-right-radius: 16px;">
                    <h5 class="modal-title fw-bold" id="nonCashPaymentModalLabel">Pembayaran Non-Tunai</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" id="btn-close-payment-modal" style="display: none;"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    
                    <!-- QRIS Container -->
                    <div id="qris-payment-container" style="display: none;">
                        <h6 class="fw-bold mb-2">Pindai Kode QRIS untuk Membayar</h6>
                        <div class="d-flex justify-content-center my-3 position-relative">
                            <div class="p-2 border border-2 border-primary bg-white rounded shadow-sm">
                                <img id="qris-image" src="" alt="QRIS Code" style="width: 200px; height: 200px; object-fit: contain;">
                            </div>
                        </div>
                        <div class="mb-3 text-muted font-monospace" style="font-size: 0.9rem;">
                            Total Tagihan: <span class="fw-bold text-primary fs-5" id="qris-total-text">Rp 0</span>
                        </div>
                        <div class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 fs-6 rounded-pill mb-2">
                            <i class="bi bi-stopwatch me-1.5 animate-pulse"></i> Batas Pembayaran: <span id="qris-timer" class="fw-bold font-monospace">01:00</span>
                        </div>
                        <p class="small text-muted mt-2">QRIS otomatis mendeteksi pembayaran dalam 5 detik...</p>
                    </div>

                    <!-- Debit / Credit Container -->
                    <div id="card-payment-container" style="display: none;">
                        <h6 class="fw-bold mb-3">Simulasi Mesin EDC (Debit / Kredit)</h6>
                        <div class="p-3 bg-light border rounded text-start mb-3" style="font-size: 0.85rem;">
                            <div class="mb-2">
                                <label class="form-label fw-bold text-muted mb-1" style="font-size: 0.75rem;">JENIS KARTU</label>
                                <select class="form-select form-select-sm" id="card-type-select">
                                    <option value="debit">Kartu Debit</option>
                                    <option value="kredit">Kartu Kredit</option>
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="form-label fw-bold text-muted mb-1" style="font-size: 0.75rem;">NAMA BANK PENERBIT</label>
                                <input type="text" class="form-control form-control-sm" id="card-bank-input" placeholder="Contoh: BCA, Mandiri, BNI..." value="BCA">
                            </div>
                            <div class="mb-2">
                                <label class="form-label fw-bold text-muted mb-1" style="font-size: 0.75rem;">NOMOR KARTU (DUMMY)</label>
                                <input type="text" class="form-control form-control-sm font-monospace" id="card-number-input" placeholder="xxxx-xxxx-xxxx-xxxx" value="4111-2222-3333-4444">
                            </div>
                        </div>
                        <button type="button" class="btn btn-primary w-100 fw-bold" id="btn-submit-card-payment">GESEK / MASUKKAN KARTU</button>
                    </div>

                    <!-- Transfer Container -->
                    <div id="transfer-payment-container" style="display: none;">
                        <h6 class="fw-bold mb-3 text-start">Pilih Rekening Bank Perusahaan (PT SouthMart)</h6>
                        <div class="list-group text-start mb-3">
                            <div class="list-group-item list-group-item-action p-2.5 border-1 rounded mb-1 bg-white select-bank-item active" data-bank="BRI" style="cursor: pointer;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold text-primary">Bank BRI</span>
                                    <span class="font-monospace fw-semibold" style="font-size: 0.9rem;">0123-4567-89-01-2</span>
                                </div>
                                <div class="small text-muted" style="font-size: 0.75rem; margin-top: 2px;">a.n. PT SouthMart Indonesia Jaya</div>
                            </div>
                            <div class="list-group-item list-group-item-action p-2.5 border-1 rounded mb-1 bg-white select-bank-item" data-bank="MANDIRI" style="cursor: pointer;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold text-primary">Bank MANDIRI</span>
                                    <span class="font-monospace fw-semibold" style="font-size: 0.9rem;">123-45-6789-012-3</span>
                                </div>
                                <div class="small text-muted" style="font-size: 0.75rem; margin-top: 2px;">a.n. PT SouthMart Indonesia Jaya</div>
                            </div>
                            <div class="list-group-item list-group-item-action p-2.5 border-1 rounded mb-1 bg-white select-bank-item" data-bank="BCA" style="cursor: pointer;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold text-primary">Bank BCA</span>
                                    <span class="font-monospace fw-semibold" style="font-size: 0.9rem;">8829-0192-38</span>
                                </div>
                                <div class="small text-muted" style="font-size: 0.75rem; margin-top: 2px;">a.n. PT SouthMart Indonesia Jaya</div>
                            </div>
                            <div class="list-group-item list-group-item-action p-2.5 border-1 rounded bg-white select-bank-item" data-bank="BNI" style="cursor: pointer;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold text-primary">Bank BNI</span>
                                    <span class="font-monospace fw-semibold" style="font-size: 0.9rem;">990-281-923-8</span>
                                </div>
                                <div class="small text-muted" style="font-size: 0.75rem; margin-top: 2px;">a.n. PT SouthMart Indonesia Jaya</div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-primary w-100 fw-bold" id="btn-submit-transfer-payment">KONFIRMASI TRANSFER</button>
                    </div>

                    <!-- Processing/Loading Screen (Used by all non-cash methods for 5 seconds) -->
                    <div id="payment-processing-container" style="display: none;">
                        <div class="spinner-border text-primary my-4" role="status" style="width: 3rem; height: 3rem;"></div>
                        <h5 class="fw-bold mb-1">Memproses Pembayaran...</h5>
                        <p class="text-muted" id="processing-message">Sedang menghubungkan ke server perbankan/QRIS...</p>
                        <div class="progress mt-3 mb-2" style="height: 6px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%" id="payment-progress-bar"></div>
                        </div>
                        <span class="text-muted small" id="processing-timer-text">Sisa waktu: 5 detik</span>
                    </div>

                    <!-- Success Screen -->
                    <div id="payment-success-container" style="display: none;">
                        <i class="bi bi-check-circle-fill text-success my-3" style="font-size: 4rem;"></i>
                        <h4 class="fw-bold text-success">Pembayaran Berhasil!</h4>
                        <p class="text-muted mb-4" id="success-tx-code-text">Transaksi selesai dan terekam di sistem.</p>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-primary flex-fill fw-bold" id="btn-print-receipt-directly"><i class="bi bi-printer me-1"></i> CETAK STRUK</button>
                            <button type="button" class="btn btn-success flex-fill fw-bold" id="btn-go-to-receipt">LIHAT STRUK</button>
                        </div>
                    </div>

                </div>
                <div class="modal-footer border-0 d-flex justify-content-between" id="payment-modal-footer">
                    <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal" id="btn-cancel-payment">BATAL</button>
                    <span class="text-muted small fw-medium" id="payment-modal-help">Metode: <span class="text-uppercase fw-bold" id="payment-method-help-text"></span></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Master Products Object for JS access -->
    <script>
        const storeProducts = {!! json_encode($products) !!};
        const checkoutRoute = "{{ route('kasir.checkout') }}";
        const csrfToken = "{{ csrf_token() }}";
    </script>
    
    <!-- JS Logic -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let cart = [];
            let paymentMethod = 'tunai';
            let grandTotal = 0;
            let selectedCategory = 'all';
            let searchQuery = '';

            const catalogSearch = document.getElementById('catalog-search');
            const categoryTabs = document.querySelectorAll('.category-tab');
            const productCards = document.querySelectorAll('.product-card');
            const cartTbody = document.getElementById('cart-tbody');
            const grandTotalText = document.getElementById('grand-total-text');
            const amountPaidInput = document.getElementById('amount-paid-input');
            const changeDisplay = document.getElementById('change-display');
            const cashSection = document.getElementById('cash-input-section');
            const changeSection = document.getElementById('change-section');

            // 1. Filtering Logic
            catalogSearch.addEventListener('input', function() {
                searchQuery = this.value.toLowerCase().trim();
                filterCatalog();
            });

            categoryTabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    categoryTabs.forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                    selectedCategory = this.getAttribute('data-category');
                    filterCatalog();
                });
            });

            function filterCatalog() {
                productCards.forEach(card => {
                    const id = card.getAttribute('data-id');
                    const catId = card.getAttribute('data-category');
                    const name = card.getAttribute('data-name').toLowerCase();
                    const sku = card.getAttribute('data-sku').toLowerCase();
                    const barcode = card.getAttribute('data-barcode').toLowerCase();

                    // Check category match
                    const matchesCategory = (selectedCategory === 'all' || catId === selectedCategory);
                    
                    // Check search query match
                    const matchesSearch = (searchQuery === '' || 
                                           name.includes(searchQuery) || 
                                           sku.includes(searchQuery) || 
                                           barcode.includes(searchQuery));

                    if (matchesCategory && matchesSearch) {
                        card.style.display = 'flex';
                    } else {
                        card.style.display = 'none';
                    }
                });
            }

            // 2. Click Product Card to Add to Cart
            productCards.forEach(card => {
                card.addEventListener('click', function() {
                    const id = parseInt(this.getAttribute('data-id'));
                    const barcode = this.getAttribute('data-barcode');
                    const name = this.getAttribute('data-name');
                    const price = parseFloat(this.getAttribute('data-price'));
                    const stock = parseInt(this.getAttribute('data-stock'));

                    if(stock === 0) {
                        alert('Stok di node cabang habis!');
                        return;
                    }

                    // Check if product is already in cart
                    const cartItem = cart.find(item => item.id === id);
                    if(cartItem) {
                        // Check stock limit
                        if(cartItem.qty + 1 > stock) {
                            alert('Stok di node cabang tidak mencukupi!');
                            return;
                        }
                        cartItem.qty++;
                    } else {
                        cart.push({
                            id: id,
                            name: name,
                            barcode: barcode,
                            price: price,
                            qty: 1
                        });
                    }

                    renderCart();
                });
            });

            // 3. Render Cart
            function renderCart() {
                cartTbody.innerHTML = '';
                
                if(cart.length === 0) {
                    cartTbody.innerHTML = `
                        <tr>
                            <td colspan="4" class="text-center text-muted py-5">Keranjang kosong. Klik produk di sebelah kiri untuk menambah.</td>
                        </tr>
                    `;
                    document.getElementById('cart-subtotal').innerText = 'Rp 0';
                    document.getElementById('cart-tax').innerText = 'Rp 0';
                    grandTotalText.innerText = 'Rp 0';
                    grandTotal = 0;
                    calculateChange();
                    return;
                }

                let subtotal = 0;

                cart.forEach((item, index) => {
                    const itemSubtotal = item.price * item.qty;
                    subtotal += itemSubtotal;

                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>
                            <div class="fw-bold text-dark">${item.name}</div>
                            <div class="text-muted small" style="font-size: 0.72rem;">Rp ${formatNumber(item.price)}</div>
                        </td>
                        <td class="text-center">
                            <div class="input-group input-group-sm justify-content-center">
                                <button class="btn btn-outline-secondary px-2 btn-minus" data-index="${index}" style="padding: 1px 6px;">-</button>
                                <span class="px-2.5 py-0.5 border text-dark fw-bold" style="font-size: 0.8rem;">${item.qty}</span>
                                <button class="btn btn-outline-secondary px-2 btn-plus" data-index="${index}" style="padding: 1px 6px;">+</button>
                            </div>
                        </td>
                        <td class="text-end fw-semibold">Rp ${formatNumber(itemSubtotal)}</td>
                        <td class="text-center">
                            <button class="btn btn-sm text-danger border-0 bg-transparent btn-remove" data-index="${index}">
                                <i class="bi bi-trash3-fill"></i>
                            </button>
                        </td>
                    `;
                    cartTbody.appendChild(row);
                });

                const tax = Math.round(subtotal * 0.11);
                grandTotal = subtotal + tax;

                document.getElementById('cart-subtotal').innerText = 'Rp ' + formatNumber(subtotal);
                document.getElementById('cart-tax').innerText = 'Rp ' + formatNumber(tax);
                grandTotalText.innerText = 'Rp ' + formatNumber(grandTotal);

                calculateChange();

                // Attach event listeners for buttons inside cart table
                document.querySelectorAll('.btn-minus').forEach(btn => {
                    btn.addEventListener('click', function(e) {
                        e.stopPropagation();
                        const index = this.getAttribute('data-index');
                        if(cart[index].qty > 1) {
                            cart[index].qty--;
                        } else {
                            cart.splice(index, 1);
                        }
                        renderCart();
                    });
                });

                document.querySelectorAll('.btn-plus').forEach(btn => {
                    btn.addEventListener('click', function(e) {
                        e.stopPropagation();
                        const index = this.getAttribute('data-index');
                        const product = storeProducts.find(p => p.id === cart[index].id);
                        if(cart[index].qty + 1 > product.stock) {
                            alert('Stok di node cabang tidak mencukupi!');
                            return;
                        }
                        cart[index].qty++;
                        renderCart();
                    });
                });

                document.querySelectorAll('.btn-remove').forEach(btn => {
                    btn.addEventListener('click', function(e) {
                        e.stopPropagation();
                        const index = this.getAttribute('data-index');
                        cart.splice(index, 1);
                        renderCart();
                    });
                });
            }

            // Handle Payment Method selection
            document.querySelectorAll('.payment-method-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.querySelectorAll('.payment-method-btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    paymentMethod = this.getAttribute('data-method');

                    if(paymentMethod === 'tunai') {
                        cashSection.style.display = 'block';
                        changeSection.style.display = 'block';
                    } else {
                        cashSection.style.display = 'none';
                        changeSection.style.display = 'none';
                        amountPaidInput.value = grandTotal;
                    }
                    calculateChange();
                });
            });

            // Calculate change
            amountPaidInput.addEventListener('input', calculateChange);

            function calculateChange() {
                if(paymentMethod !== 'tunai') {
                    changeDisplay.innerText = 'Rp 0';
                    return;
                }

                const paid = parseFloat(amountPaidInput.value) || 0;
                const change = paid - grandTotal;
                
                if(change < 0) {
                    changeDisplay.innerText = 'Rp -' + formatNumber(Math.abs(change));
                    changeDisplay.style.color = '#EF4444'; // Red
                } else {
                    changeDisplay.innerText = 'Rp ' + formatNumber(change);
                    changeDisplay.style.color = '#10B981'; // Green
                }
            }

            // Quick cash buttons
            document.querySelectorAll('.quick-cash-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const amount = parseFloat(this.getAttribute('data-cash'));
                    const currentPaid = parseFloat(amountPaidInput.value) || 0;
                    amountPaidInput.value = currentPaid + amount;
                    calculateChange();
                });
            });

            // Exact Cash Button
            document.getElementById('btn-exact-cash').addEventListener('click', function() {
                amountPaidInput.value = grandTotal;
                calculateChange();
            });

            // Reset Cash Button
            document.getElementById('btn-clear-cash').addEventListener('click', function() {
                amountPaidInput.value = '';
                calculateChange();
            });

            // Modal Elements
            const paymentModalEl = document.getElementById('nonCashPaymentModal');
            const paymentModal = new bootstrap.Modal(paymentModalEl);
            const btnClosePaymentModal = document.getElementById('btn-close-payment-modal');
            const btnCancelPayment = document.getElementById('btn-cancel-payment');
            
            const qrisContainer = document.getElementById('qris-payment-container');
            const cardContainer = document.getElementById('card-payment-container');
            const transferContainer = document.getElementById('transfer-payment-container');
            const processingContainer = document.getElementById('payment-processing-container');
            const successContainer = document.getElementById('payment-success-container');
            
            const qrisImage = document.getElementById('qris-image');
            const qrisTotalText = document.getElementById('qris-total-text');
            const qrisTimerSpan = document.getElementById('qris-timer');
            const paymentMethodHelpText = document.getElementById('payment-method-help-text');
            const paymentProgressBar = document.getElementById('payment-progress-bar');
            const processingTimerText = document.getElementById('processing-timer-text');
            const successTxCodeText = document.getElementById('success-tx-code-text');
            const btnGoToReceipt = document.getElementById('btn-go-to-receipt');
            const btnSubmitCardPayment = document.getElementById('btn-submit-card-payment');
            const btnSubmitTransferPayment = document.getElementById('btn-submit-transfer-payment');

            let qrisCountdownInterval = null;
            let processingInterval = null;
            let qrisAutoTimeout = null;

            // Bank item selection styling
            document.querySelectorAll('.select-bank-item').forEach(item => {
                item.addEventListener('click', function() {
                    document.querySelectorAll('.select-bank-item').forEach(i => i.classList.remove('active'));
                    this.classList.add('active');
                });
            });

            function resetModalContainers() {
                qrisContainer.style.display = 'none';
                cardContainer.style.display = 'none';
                transferContainer.style.display = 'none';
                processingContainer.style.display = 'none';
                successContainer.style.display = 'none';
                
                // Show cancel button, hide close icon
                btnCancelPayment.style.display = 'inline-block';
                btnClosePaymentModal.style.display = 'none';
                document.getElementById('payment-modal-footer').style.display = 'flex';
                
                // Clear any timers/intervals
                if (qrisCountdownInterval) clearInterval(qrisCountdownInterval);
                if (processingInterval) clearInterval(processingInterval);
                if (qrisAutoTimeout) clearTimeout(qrisAutoTimeout);
            }

            // Cleanup on modal hide
            paymentModalEl.addEventListener('hidden.bs.modal', function () {
                resetModalContainers();
            });

            // Checkout Process
            document.getElementById('btn-process-checkout').addEventListener('click', function() {
                if(cart.length === 0) {
                    alert('Keranjang belanja kosong! Klik produk di sebelah kiri.');
                    return;
                }

                const paid = parseFloat(amountPaidInput.value) || 0;
                if(paymentMethod === 'tunai' && paid < grandTotal) {
                    alert('Uang bayar kurang!');
                    return;
                }

                if (paymentMethod === 'tunai') {
                    // Direct cash checkout
                    this.disabled = true;
                    this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memproses...';

                    const payload = {
                        cart: cart,
                        payment_method: paymentMethod,
                        amount_paid: paid
                    };

                    fetch(checkoutRoute, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify(payload)
                    })
                    .then(res => res.json())
                    .then(data => {
                        if(data.success) {
                            alert('Transaksi Berhasil!');
                            window.location.href = `/kasir/receipt/${data.transaction_code}`;
                        } else {
                            alert('Gagal: ' + data.message);
                            this.disabled = false;
                            this.innerHTML = 'PROSES TRANSAKSI';
                        }
                    })
                    .catch(err => {
                        alert('Kesalahan jaringan: ' + err.message);
                        this.disabled = false;
                        this.innerHTML = 'PROSES TRANSAKSI';
                    });
                } else {
                    // Non-cash flow with interactive UI
                    resetModalContainers();
                    paymentMethodHelpText.innerText = paymentMethod;
                    
                    if (paymentMethod === 'qris') {
                        // QRIS Flow
                        qrisTotalText.innerText = 'Rp ' + formatNumber(grandTotal);
                        
                        // Generate mock QRIS containing dummy payment code
                        const dummyCode = 'SOUTHMART-QRIS-' + Date.now() + '-' + grandTotal;
                        qrisImage.src = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' + encodeURIComponent(dummyCode);
                        
                        qrisContainer.style.display = 'block';
                        paymentModal.show();
                        
                        // 1 minute timer countdown (60 seconds)
                        let timeLimit = 60;
                        qrisTimerSpan.innerText = '01:00';
                        qrisCountdownInterval = setInterval(() => {
                            timeLimit--;
                            const minutes = Math.floor(timeLimit / 60);
                            const seconds = timeLimit % 60;
                            qrisTimerSpan.innerText = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                            if (timeLimit <= 0) {
                                clearInterval(qrisCountdownInterval);
                                alert('Batas waktu pembayaran QRIS telah habis!');
                                paymentModal.hide();
                            }
                        }, 1000);
                        
                        // Auto-process to success after 2 seconds of showing QR (client scanning)
                        // followed by 5-second processing animation
                        qrisAutoTimeout = setTimeout(() => {
                            qrisContainer.style.display = 'none';
                            start5SecondProcessing("Menghubungkan ke gateway pembayaran QRIS...", () => {
                                executeCheckoutAPICall();
                            });
                        }, 2000);
                        
                    } else if (paymentMethod === 'debit' || paymentMethod === 'kredit') {
                        // Debit/Credit Card flow
                        document.getElementById('card-type-select').value = paymentMethod;
                        cardContainer.style.display = 'block';
                        paymentModal.show();
                        
                        // Click to swipe/insert card
                        btnSubmitCardPayment.onclick = function() {
                            cardContainer.style.display = 'none';
                            start5SecondProcessing("Menghubungi bank penerbit kartu...", () => {
                                executeCheckoutAPICall();
                            });
                        };
                        
                    } else if (paymentMethod === 'transfer') {
                        // Bank Transfer flow
                        transferContainer.style.display = 'block';
                        paymentModal.show();
                        
                        // Click to confirm transfer
                        btnSubmitTransferPayment.onclick = function() {
                            transferContainer.style.display = 'none';
                            start5SecondProcessing("Memverifikasi mutasi rekening bank...", () => {
                                executeCheckoutAPICall();
                            });
                        };
                    }
                }
            });

            // 5 Seconds progress bar processing animation
            function start5SecondProcessing(message, callback) {
                processingContainer.style.display = 'block';
                document.getElementById('processing-message').innerText = message;
                
                // Hide modal footer during processing
                document.getElementById('payment-modal-footer').style.display = 'none';
                
                let progress = 0;
                let sisa = 5;
                
                paymentProgressBar.style.width = '0%';
                processingTimerText.innerText = 'Sisa waktu: 5 detik';
                
                processingInterval = setInterval(() => {
                    progress += 20;
                    sisa--;
                    paymentProgressBar.style.width = progress + '%';
                    processingTimerText.innerText = `Sisa waktu: ${sisa} detik`;
                    
                    if (progress >= 100) {
                        clearInterval(processingInterval);
                        callback();
                    }
                }, 1000);
            }

            // Calls the Laravel checkout API
            function executeCheckoutAPICall() {
                const paid = paymentMethod === 'tunai' ? parseFloat(amountPaidInput.value) || 0 : grandTotal;
                
                const payload = {
                    cart: cart,
                    payment_method: paymentMethod,
                    amount_paid: paid
                };
                
                fetch(checkoutRoute, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(payload)
                })
                .then(res => res.json())
                .then(data => {
                    if(data.success) {
                        // Hide processing container
                        processingContainer.style.display = 'none';
                        
                        // Show success container
                        successContainer.style.display = 'block';
                        
                        // Show close button
                        btnClosePaymentModal.style.display = 'inline-block';
                        btnCancelPayment.style.display = 'none';
                        
                        document.getElementById('payment-modal-footer').style.display = 'flex';
                        
                        // Set success details
                        document.getElementById('success-tx-code-text').innerHTML = `Kode Transaksi: <strong class="font-monospace text-primary">${data.transaction_code}</strong><br>Metode: <span class="text-uppercase fw-bold text-success">${paymentMethod}</span>`;
                        
                        // Print directly
                        document.getElementById('btn-print-receipt-directly').onclick = function() {
                            paymentModal.hide();
                            window.open(`/kasir/receipt/${data.transaction_code}?print=true`, '_blank');
                            window.location.reload();
                        };

                        btnGoToReceipt.onclick = function() {
                            paymentModal.hide();
                            window.location.href = `/kasir/receipt/${data.transaction_code}`;
                        };
                    } else {
                        alert('Gagal: ' + data.message);
                        paymentModal.hide();
                    }
                })
                .catch(err => {
                    alert('Kesalahan jaringan: ' + err.message);
                    paymentModal.hide();
                });
            }

            // Helper format number
            function formatNumber(num) {
                return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            }
        });
    </script>
</body>
</html>
