<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Penjualan {{ $branchName }} - SouthMart</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #F8FAFC;
            color: #1E293B;
            padding: 2rem 0;
        }

        .history-container {
            max-width: 900px;
            margin: 0 auto;
        }

        .card {
            border: 1px solid #E2E8F0;
            border-radius: 12px;
            background-color: #FFFFFF;
        }
    </style>
</head>
<body>

<div class="container history-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold m-0">Riwayat Penjualan</h3>
            <span class="text-muted">{{ $branchName }}</span>
        </div>
        <a href="{{ route('kasir.pos') }}" class="btn btn-primary fw-semibold">
            <i class="bi bi-cart me-1.5"></i> Kembali ke POS
        </a>
    </div>

    <!-- Notification Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold text-dark m-0">Daftar Transaksi Lokal Cabang</h5>
            <form action="{{ route('kasir.sync-local') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-primary btn-sm fw-semibold">
                    <i class="bi bi-arrow-repeat me-1.5"></i> Sinkronisasikan Semua
                </button>
            </form>
        </div>
        
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Kode Transaksi</th>
                            <th>Metode Bayar</th>
                            <th class="text-end">Total Belanja</th>
                            <th class="text-center">Status Sinkronisasi</th>
                            <th>Tanggal</th>
                            <th class="text-center">Struk</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $tx)
                        <tr>
                            <td><strong class="text-primary font-monospace">{{ $tx->transaction_code }}</strong></td>
                            <td>
                                <span class="badge bg-secondary-subtle text-secondary border text-uppercase" style="font-size: 0.75rem;">
                                    {{ $tx->payment_method ?? 'tunai' }}
                                </span>
                            </td>
                            <td class="text-end fw-semibold">Rp {{ number_format($tx->grand_total, 0, ',', '.') }}</td>
                            <td class="text-center">
                                @if($tx->sync_status === 'synced')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">Synchronized</span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill">Pending Sync</span>
                                @endif
                            </td>
                            <td>{{ date('d-m-Y H:i:s', strtotime($tx->created_at)) }}</td>
                            <td class="text-center">
                                <a href="{{ route('kasir.receipt', $tx->transaction_code) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-receipt"></i> Buka
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-5">
                                Belum ada transaksi yang tercatat di cabang ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $transactions->links() }}
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
