@extends('layouts.admin')

@section('title', 'Monitoring Cabang')
@section('header_title', 'Monitoring Database Cabang')

@section('content')
<div class="row g-4 mb-4">
    @foreach($nodes as $node)
    @php
        $stats = $branchStats[$node->id] ?? ['tx_today' => 0, 'pending_sync' => 0];
    @endphp
    <div class="col-xl-4 col-md-6">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold m-0 text-dark">{{ $node->name }}</h5>
                @if($node->node_status === 'online')
                    <span class="badge bg-success text-white px-2.5 py-1 rounded-pill d-inline-flex align-items-center gap-1.5">
                        <span class="indicator online"></span> Online
                    </span>
                @else
                    <span class="badge bg-danger text-white px-2.5 py-1 rounded-pill d-inline-flex align-items-center gap-1.5">
                        <span class="indicator offline"></span> Offline
                    </span>
                @endif
            </div>
            
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="icon-box-accent {{ $node->node_status === 'online' ? 'icon-box-blue' : 'icon-box-red' }} fs-3">
                        <i class="bi bi-hdd-network"></i>
                    </div>
                    <div>
                        <span class="text-muted d-block small">IP Server Node</span>
                        <strong class="text-dark">{{ $node->ip_address ?? '127.0.0.1' }}</strong>
                    </div>
                </div>

                <div class="list-group list-group-flush border-top border-bottom py-2 mb-4">
                    <div class="list-group-item border-0 px-0 d-flex justify-content-between align-items-center">
                        <span class="text-muted">Koneksi Database MySQL:</span>
                        @if($node->db_status === 'online' && $node->node_status === 'online')
                            <span class="text-success fw-bold"><i class="bi bi-database-check me-1"></i> Terhubung</span>
                        @else
                            <span class="text-danger fw-bold"><i class="bi bi-database-exclamation me-1"></i> Terputus</span>
                        @endif
                    </div>
                    <div class="list-group-item border-0 px-0 d-flex justify-content-between align-items-center">
                        <span class="text-muted">Transaksi Hari Ini (Lokal):</span>
                        <span class="fw-bold text-dark">{{ $stats['tx_today'] }} Transaksi</span>
                    </div>
                    <div class="list-group-item border-0 px-0 d-flex justify-content-between align-items-center">
                        <span class="text-muted">Mengantre Sinkron (Pending):</span>
                        <span class="badge {{ $stats['pending_sync'] > 0 ? 'bg-warning text-dark' : 'bg-secondary-subtle text-secondary' }} px-2 py-1.5 rounded-pill">
                            {{ $stats['pending_sync'] }} Transaksi
                        </span>
                    </div>
                    <div class="list-group-item border-0 px-0 d-flex justify-content-between align-items-center">
                        <span class="text-muted">Sinkronisasi Terakhir:</span>
                        <span class="fw-semibold text-dark">{{ $node->last_sync ? date('d/m/Y H:i:s', strtotime($node->last_sync)) : 'Belum pernah' }}</span>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <form action="{{ route('admin.toggle-node', $node->id) }}" method="POST">
                        @csrf
                        @if($node->node_status === 'online')
                            <button type="submit" class="btn btn-outline-danger w-100 fw-semibold">
                                <i class="bi bi-cloud-slash me-2"></i> Putuskan Jaringan
                            </button>
                        @else
                            <button type="submit" class="btn btn-success text-white w-100 fw-semibold">
                                <i class="bi bi-cloud-check me-2"></i> Hubungkan Jaringan (Online)
                            </button>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection
