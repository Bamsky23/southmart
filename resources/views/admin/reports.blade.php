@extends('layouts.admin')

@section('title', 'Laporan')
@section('header_title', 'Sistem Pelaporan Terdistribusi')

@section('content')
<div class="row g-4">
    <!-- Report Generator Form -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                <h5 class="fw-bold m-0 text-dark"><i class="bi bi-file-earmark-pdf-fill me-2 text-danger"></i>Cetak Laporan Baru</h5>
                <p class="text-muted small mb-0">Pilih jenis laporan ritel/database terdistribusi untuk diekspor.</p>
            </div>
            
            <div class="card-body p-4">
                <form action="{{ route('admin.reports.generate') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="title" class="form-label fw-semibold small">Judul Laporan</label>
                        <input type="text" name="title" id="title" class="form-control" placeholder="Contoh: Laporan Penjualan Q2 Bogor" required>
                    </div>

                    <div class="mb-4">
                        <label for="type" class="form-label fw-semibold small">Jenis Laporan</label>
                        <select name="type" id="type" class="form-select" required>
                            <option value="">Pilih Jenis</option>
                            <option value="sales_branch">Laporan Penjualan Cabang</option>
                            <option value="sales_national">Laporan Penjualan Nasional</option>
                            <option value="replication">Laporan Status Node & Replikasi</option>
                            <option value="consistency">Laporan Konsistensi Data</option>
                        </select>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary py-2.5 fw-bold shadow-sm">
                            <i class="bi bi-plus-lg me-1.5"></i> Buat Laporan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Generated Reports List -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                <h5 class="fw-bold m-0 text-dark"><i class="bi bi-folder-fill me-2 text-warning"></i>Arsip Dokumen Laporan</h5>
            </div>
            
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Judul Laporan</th>
                                <th>Jenis</th>
                                <th>Tanggal Dibuat</th>
                                <th class="text-center">Unduh PDF</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reports as $rep)
                            <tr>
                                <td><strong>{{ $rep->title }}</strong></td>
                                <td>
                                    @switch($rep->type)
                                        @case('sales_branch')
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">Penjualan Cabang</span>
                                            @break
                                        @case('sales_national')
                                            <span class="badge bg-success-subtle text-success border border-success-subtle">Penjualan Nasional</span>
                                            @break
                                        @case('replication')
                                            <span class="badge bg-info-subtle text-info border border-info-subtle">Node & Replikasi</span>
                                            @break
                                        @case('consistency')
                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Konsistensi Data</span>
                                            @break
                                        @default
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Umum</span>
                                    @endswitch
                                </td>
                                <td>{{ date('d-m-Y H:i:s', strtotime($rep->created_at)) }}</td>
                                <td class="text-center">
                                    <a href="{{ route('admin.reports.download', $rep->id) }}" class="btn btn-sm btn-outline-danger fw-semibold" target="_blank">
                                        <i class="bi bi-file-earmark-pdf me-1"></i> PDF
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-5">
                                    <i class="bi bi-file-earmark-excel fs-1 d-block mb-3 text-muted-50"></i>
                                    Belum ada laporan yang dibuat. Silakan gunakan panel kiri untuk membuat laporan.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
