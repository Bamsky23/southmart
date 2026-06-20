@extends('layouts.admin')

@section('title', 'Penjualan Nasional')
@section('header_title', 'Konsolidasi Penjualan Nasional')

@section('content')
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold m-0 text-dark"><i class="bi bi-cart-check-fill me-2 text-primary"></i>Daftar Transaksi Konsolidasian</h5>
        <span class="badge bg-primary px-3 py-2 fs-7">Data Sinkron Terkini</span>
    </div>
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Kode Transaksi</th>
                        <th>Cabang</th>
                        <th>Kasir</th>
                        <th class="text-end">Subtotal</th>
                        <th class="text-end">Pajak (11%)</th>
                        <th class="text-end font-monospace fw-bold">Total Belanja</th>
                        <th class="text-center">Status Sinkron</th>
                        <th>Tanggal Transaksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $tx)
                    <tr>
                        <td><strong class="text-primary">{{ $tx->transaction_code }}</strong></td>
                        <td>{{ $tx->branch_name }}</td>
                        <td>{{ $tx->cashier_name }}</td>
                        <td class="text-end">Rp {{ number_format($tx->total_price, 0, ',', '.') }}</td>
                        <td class="text-end">Rp {{ number_format($tx->tax, 0, ',', '.') }}</td>
                        <td class="text-end font-monospace fw-bold text-dark">Rp {{ number_format($tx->grand_total, 0, ',', '.') }}</td>
                        <td class="text-center">
                            @if($tx->sync_status === 'synced')
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">Synchronized</span>
                            @else
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill">Pending Sync</span>
                            @endif
                        </td>
                        <td>{{ date('d-m-Y H:i:s', strtotime($tx->created_at)) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-5">
                            <i class="bi bi-cart-x fs-1 d-block mb-3 text-muted-50"></i>
                            Belum ada transaksi konsolidasian di server pusat.
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
@endsection
