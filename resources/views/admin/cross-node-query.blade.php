@extends('layouts.admin')

@section('title', 'Query Lintas Node')
@section('header_title', 'Query Lintas Node Database')

@section('content')
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-4 text-center">
        <h4 class="fw-bold text-dark mb-2">Query Terdistribusi</h4>
        <p class="text-muted mx-auto" style="max-width: 600px;">
        </p>
        <div class="mt-4">
            <a href="{{ route('admin.cross-node-query') }}?run=1" class="btn btn-primary btn-lg px-5 py-3 fw-bold shadow">
                <i class="bi bi-play-circle-fill me-2 fs-5"></i> Ambil Data Rekapitulasi Nasional
            </a>
        </div>
    </div>
</div>

@if($execute && $queryResult)
<div class="row g-4 mb-4">
    <!-- National Revenue -->
    <div class="col-xl-3 col-sm-6">
        <div class="dashboard-card border-primary-subtle bg-primary-subtle text-primary">
            <span class="fw-bold small d-block mb-1">Total Omzet Nasional</span>
            <h3 class="fw-extrabold m-0">Rp {{ number_format($queryResult['total_omzet'], 0, ',', '.') }}</h3>
            <small class="text-primary-emphasis">Agregasi SQL SUM()</small>
        </div>
    </div>

    <!-- National Tx Count -->
    <div class="col-xl-3 col-sm-6">
        <div class="dashboard-card border-success-subtle bg-success-subtle text-success">
            <span class="fw-bold small d-block mb-1">Total Transaksi Nasional</span>
            <h3 class="fw-extrabold m-0">{{ number_format($queryResult['total_tx'], 0, ',', '.') }}</h3>
            <small class="text-success-emphasis">Agregasi SQL COUNT()</small>
        </div>
    </div>

    <!-- National Qty Sold -->
    <div class="col-xl-3 col-sm-6">
        <div class="dashboard-card border-info-subtle bg-info-subtle text-info">
            <span class="fw-bold small d-block mb-1">Total Produk Terjual</span>
            <h3 class="fw-extrabold m-0">{{ number_format($queryResult['total_qty'], 0, ',', '.') }}</h3>
            <small class="text-info-emphasis">Agregasi SQL SUM(qty)</small>
        </div>
    </div>

    <!-- Best Branch -->
    <div class="col-xl-3 col-sm-6">
        <div class="dashboard-card border-warning-subtle bg-warning-subtle text-warning">
            <span class="fw-bold small d-block mb-1">Cabang Terbaik (Omzet)</span>
            <h3 class="fw-extrabold m-0 text-truncate">{{ $queryResult['best_branch'] }}</h3>
            <small class="text-warning-emphasis">Rp {{ number_format($queryResult['best_branch_revenue'], 0, ',', '.') }}</small>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Shards list status -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                <h5 class="fw-bold m-0 text-dark"><i class="bi bi-hdd-network-fill me-2 text-primary"></i>Rincian Hasil Query per Database Shard</h5>
            </div>
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Nama Database Shard</th>
                                <th class="text-end">Omzet Shard</th>
                                <th class="text-center">Transaksi Shard</th>
                                <th class="text-center">Unit Terjual</th>
                                <th class="text-center">Status Koneksi Query</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($queryResult['details'] as $branchName => $stats)
                            <tr>
                                <td><strong>{{ $branchName }}</strong></td>
                                <td class="text-end">Rp {{ number_format($stats['revenue'], 0, ',', '.') }}</td>
                                <td class="text-center fw-medium">{{ $stats['transactions'] }}</td>
                                <td class="text-center">{{ $stats['qty'] }}</td>
                                <td class="text-center">
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">🟢 Berhasil diquery</span>
                                </td>
                            </tr>
                            @endforeach
                            
                            @foreach($queryResult['nodes_skipped'] as $skippedNode)
                            <tr class="table-danger-subtle">
                                <td class="text-danger"><strong>{{ $skippedNode }}</strong></td>
                                <td class="text-end text-muted">Rp 0</td>
                                <td class="text-center text-muted">0</td>
                                <td class="text-center text-muted">0</td>
                                <td class="text-center">
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill">🔴 Offline (Dilewati)</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Query Stats -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                <h5 class="fw-bold m-0 text-dark"><i class="bi bi-clock-history me-2 text-primary"></i>Metadata Eksekusi</h5>
            </div>
            <div class="card-body p-4">
                <div class="list-group list-group-flush">
                    <div class="list-group-item px-0 d-flex justify-content-between">
                        <span class="text-muted">Waktu Eksekusi:</span>
                        <strong class="text-dark">{{ date('H:i:s', strtotime($queryResult['timestamp'])) }}</strong>
                    </div>
                    <div class="list-group-item px-0 d-flex justify-content-between">
                        <span class="text-muted">Metode Query:</span>
                        <strong class="text-primary font-monospace">Union / Horizontal Map</strong>
                    </div>
                    <div class="list-group-item px-0 d-flex justify-content-between">
                        <span class="text-muted">Node Dihubungi:</span>
                        <strong class="text-success">{{ count($queryResult['nodes_queried']) }} / {{ $branchCount }} Cabang</strong>
                    </div>
                    <div class="list-group-item px-0 d-flex justify-content-between">
                        <span class="text-muted">Node Gagal/Offline:</span>
                        <strong class="text-danger">{{ count($queryResult['nodes_skipped']) }} Cabang</strong>
                    </div>
                    <div class="list-group-item px-0 d-flex justify-content-between">
                        <span class="text-muted">Tipe Fragmentasi:</span>
                        <strong class="text-dark">Horizontal (branch_id)</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection
