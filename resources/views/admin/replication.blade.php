@extends('layouts.admin')

@section('title', 'Replikasi & Konsistensi')
@section('header_title', 'Replikasi & Konsistensi Database')

@section('content')
<div class="row g-4 mb-4">
    <!-- Consistency Audit Panel -->
    <div class="col-lg-12">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold m-0 text-dark"><i class="bi bi-shield-check-fill me-2 text-primary"></i>Hasil Pengujian Konsistensi Data Terbaru</h5>
                <form action="{{ route('admin.check-consistency') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline-primary btn-sm fw-semibold">
                        <i class="bi bi-arrow-repeat me-1"></i> Audit Konsistensi Sekarang
                    </button>
                </form>
            </div>
            
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Cabang</th>
                                <th>Nama Tabel</th>
                                <th class="text-center">Jumlah Baris (Cabang)</th>
                                <th class="text-center">Jumlah Baris (Pusat)</th>
                                <th class="text-center">Persentase Cocok</th>
                                <th class="text-center">Status Audit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($consistencyChecks as $check)
                            @php
                                $isConsistent = $check->is_consistent;
                                $percentage = $check->percentage;
                                $statusClass = $isConsistent ? 'bg-success-subtle text-success border-success-subtle' : 'bg-danger-subtle text-danger border-danger-subtle';
                                $statusText = $isConsistent ? 'Consistent' : 'Inconsistent';
                            @endphp
                            <tr>
                                <td><strong>{{ $check->branch_name }}</strong></td>
                                <td><code class="text-dark fw-medium">{{ $check->table_name }}</code></td>
                                <td class="text-center fw-semibold">{{ $check->branch_count }}</td>
                                <td class="text-center fw-semibold">{{ $check->central_count }}</td>
                                <td class="text-center">
                                    <div class="d-flex align-items-center justify-content-center gap-2">
                                        <div class="progress w-50" style="height: 6px;">
                                            <div class="progress-bar {{ $isConsistent ? 'bg-success' : 'bg-danger' }}" style="width: {{ $percentage }}%"></div>
                                        </div>
                                        <span class="small fw-bold">{{ number_format($percentage, 2) }}%</span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge {{ $statusClass }} border rounded-pill">{{ $statusText }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">
                                    <i class="bi bi-shield-exclamation fs-1 d-block mb-3 text-muted-50"></i>
                                    Belum ada data audit konsistensi. Tekan tombol di atas untuk memulai audit.
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

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold m-0 text-dark"><i class="bi bi-arrow-repeat me-2 text-primary"></i>Log Replikasi Data Master</h5>
        <form action="{{ route('admin.run-replication') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-primary btn-sm fw-semibold">
                <i class="bi bi-copy me-1"></i> Replikasi Data Master
            </button>
        </form>
    </div>
    
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Cabang</th>
                        <th>Tabel Master</th>
                        <th class="text-center">Baris Dikirim</th>
                        <th class="text-center">Baris Diterima</th>
                        <th>Waktu Transmisi</th>
                        <th class="text-center">Hasil Replikasi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($repLogs as $log)
                    <tr>
                        <td><strong>{{ $log->branch_name }}</strong></td>
                        <td><code class="text-dark fw-medium">{{ $log->table_name }}</code></td>
                        <td class="text-center fw-medium">{{ $log->records_sent }}</td>
                        <td class="text-center fw-medium">{{ $log->records_received }}</td>
                        <td>{{ date('d-m-Y H:i:s', strtotime($log->created_at)) }}</td>
                        <td class="text-center">
                            @if($log->status === 'success')
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">🟢 Success</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill" title="{{ $log->error_message }}">🔴 Failed</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">
                            Belum ada log replikasi terekam.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="mt-3">
            {{ $repLogs->links() }}
        </div>
    </div>
</div>
@endsection
