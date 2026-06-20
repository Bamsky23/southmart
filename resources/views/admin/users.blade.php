@extends('layouts.admin')

@section('title', 'Daftar Pengguna')
@section('header_title', 'Pengelolaan Pengguna Sistem')

@section('content')
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
        <h5 class="fw-bold m-0 text-dark"><i class="bi bi-people-fill me-2 text-primary"></i>Pengguna Terdaftar</h5>
        <p class="text-muted small mb-0">Daftar administrator pusat dan kasir cabang yang berwenang mengakses sistem SouthMart.</p>
    </div>
    
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width: 80px;">ID</th>
                        <th>Nama Pengguna</th>
                        <th>Alamat Email</th>
                        <th>Hak Akses / Peran</th>
                        <th>Penugasan Cabang</th>
                        <th>Tanggal Terdaftar</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $u)
                    <tr>
                        <td class="text-center text-muted">#{{ $u->id }}</td>
                        <td><strong>{{ $u->name }}</strong></td>
                        <td>{{ $u->email }}</td>
                        <td>
                            @if($u->role === 'admin')
                                <span class="badge bg-primary px-2.5 py-1.5 rounded-pill"><i class="bi bi-shield-lock me-1"></i> ADMIN PUSAT</span>
                            @else
                                <span class="badge bg-secondary px-2.5 py-1.5 rounded-pill"><i class="bi bi-person me-1"></i> KASIR CABANG</span>
                            @endif
                        </td>
                        <td>
                            @if($u->branch)
                                <span class="fw-semibold text-dark">{{ $u->branch->name }}</span>
                                <div class="small text-muted" style="font-size: 0.75rem;">Kode: {{ $u->branch->code }}</div>
                            @else
                                <span class="text-muted font-monospace">- Kantor Pusat -</span>
                            @endif
                        </td>
                        <td>{{ date('d-m-Y', strtotime($u->created_at)) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
