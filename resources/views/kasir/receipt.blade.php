<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk {{ $tx->transaction_code }} - SouthMart</title>
    <link href="https://fonts.googleapis.com/css2?family=Courier+Prime:wght@400;700&family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #F1F5F9;
            padding: 2rem 0;
        }

        .receipt-container {
            max-width: 450px;
            margin: 0 auto;
            background-color: #FFFFFF;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border-radius: 12px;
            padding: 2rem;
        }

        .thermal-receipt {
            font-family: 'Courier Prime', monospace;
            color: #000000;
        }

        .receipt-title {
            text-align: center;
            font-weight: 700;
            font-size: 1.4rem;
            margin-bottom: 0.2rem;
        }

        .receipt-subtitle {
            text-align: center;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .divider {
            border-top: 1px dashed #000000;
            margin: 0.8rem 0;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            font-size: 0.85rem;
            margin-bottom: 0.2rem;
        }

        .item-row {
            font-size: 0.85rem;
            margin-bottom: 0.4rem;
        }

        .item-details {
            display: flex;
            justify-content: space-between;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            font-size: 0.95rem;
            font-weight: 700;
            margin-bottom: 0.2rem;
        }

        .grand-total-row {
            display: flex;
            justify-content: space-between;
            font-size: 1.1rem;
            font-weight: 700;
            margin-top: 0.5rem;
        }

        @media print {
            body {
                background-color: #FFFFFF;
                padding: 0;
            }
            .receipt-container {
                box-shadow: none;
                max-width: 100%;
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="receipt-container">
        
        <!-- Action buttons (Not printed) -->
        <div class="d-flex justify-content-between mb-4 no-print">
            <a href="{{ route('kasir.pos') }}" class="btn btn-outline-secondary btn-sm fw-semibold">
                <i class="bi bi-arrow-left"></i> POS Baru
            </a>
            <div>
                <button onclick="window.print()" class="btn btn-primary btn-sm fw-semibold me-1">
                    <i class="bi bi-printer"></i> Cetak Struk
                </button>
                <a href="{{ route('kasir.receipt.download', $tx->transaction_code) }}" class="btn btn-danger btn-sm fw-semibold" target="_blank">
                    <i class="bi bi-file-earmark-pdf"></i> Unduh PDF
                </a>
            </div>
        </div>

        <!-- THERMAL RECEIPT CONTENT -->
        <div class="thermal-receipt">
            <div class="receipt-title">SouthMart</div>
            <div class="receipt-subtitle">{{ $branchName }}</div>
            
            <div class="info-row">
                <span>No. Transaksi:</span>
                <span>{{ $tx->transaction_code }}</span>
            </div>
            <div class="info-row">
                <span>Tanggal:</span>
                <span>{{ date('d-m-Y H:i', strtotime($tx->created_at)) }}</span>
            </div>
            <div class="info-row">
                <span>Kasir:</span>
                <span>{{ Auth::user()->name }}</span>
            </div>
            
            <div class="divider"></div>

            <!-- Items -->
            @foreach($details as $item)
            <div class="item-row">
                <div class="fw-bold">{{ $item->product_name }}</div>
                <div class="item-details text-muted">
                    <span>{{ $item->quantity }} x Rp {{ number_format($item->price, 0, ',', '.') }}</span>
                    <span>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                </div>
            </div>
            @endforeach

            <div class="divider"></div>

            <!-- Calculations -->
            <div class="info-row">
                <span>Subtotal:</span>
                <span>Rp {{ number_format($tx->total_price, 0, ',', '.') }}</span>
            </div>
            <div class="info-row">
                <span>Pajak (PPN 11%):</span>
                <span>Rp {{ number_format($tx->tax, 0, ',', '.') }}</span>
            </div>
            
            <div class="divider"></div>

            <div class="grand-total-row">
                <span>TOTAL:</span>
                <span>Rp {{ number_format($tx->grand_total, 0, ',', '.') }}</span>
            </div>

            <div class="divider"></div>

            <!-- Payment details -->
            <div class="info-row">
                <span>Metode Bayar:</span>
                <span class="text-uppercase">{{ $payment->method ?? 'tunai' }}</span>
            </div>
            <div class="info-row">
                <span>Bayar:</span>
                <span>Rp {{ number_format($payment->amount_paid ?? $tx->grand_total, 0, ',', '.') }}</span>
            </div>
            <div class="info-row">
                <span>Kembalian:</span>
                <span>Rp {{ number_format($payment->amount_change ?? 0, 0, ',', '.') }}</span>
            </div>

            <div class="divider"></div>

            <div class="text-center mt-4 text-uppercase fw-semibold" style="font-size: 0.85rem;">
                Terima Kasih Atas Kunjungan Anda<br>
                SouthMart Fresh Everyday!
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
@if(request()->has('print'))
<script>
    window.onload = function() {
        window.print();
    }
</script>
@endif
</body>
</html>
