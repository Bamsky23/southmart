@extends('layouts.admin')

@section('title', 'Daftar Cabang')
@section('header_title', 'Pengelolaan Cabang SouthMart')

@section('content')
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
        <h5 class="fw-bold m-0 text-dark"><i class="bi bi-shop-window me-2 text-primary"></i>Cabang Terdaftar</h5>
        <p class="text-muted small mb-0">Cabang ritel aktif di Indonesia yang terintegrasi dengan jaringan database terdistribusi SouthMart.</p>
    </div>
    
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width: 80px;">ID Node</th>
                        <th>Kode Cabang</th>
                        <th>Nama Cabang</th>
                        <th>Wilayah Operasional</th>
                        <th>Alamat IP Database Node</th>
                        <th>Tanggal Terdaftar</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($branches as $b)
                    <tr>
                        <td class="text-center"><span class="badge bg-secondary-subtle text-secondary rounded-3 fs-6 p-2">{{ $b->id }}</span></td>
                        <td class="font-monospace fw-bold text-primary">{{ $b->code }}</td>
                        <td><strong>{{ $b->name }}</strong></td>
                        <td>{{ $b->location }}</td>
                        <td class="font-monospace text-muted">{{ $b->ip_address ?? '127.0.0.1' }}</td>
                        <td>{{ date('d-m-Y', strtotime($b->created_at)) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
