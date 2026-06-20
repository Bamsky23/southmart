@extends('layouts.admin')

@section('title', 'Pengaturan')
@section('header_title', 'Konfigurasi Sistem')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                <h5 class="fw-bold m-0 text-dark"><i class="bi bi-gear-fill me-2 text-primary"></i>Pengaturan Database</h5>
                <p class="text-muted small mb-0">Atur parameter dan behavior replikasi atau delay sinkronisasi terdistribusi.</p>
            </div>
            
            <div class="card-body p-4">
                <div class="mb-4">
                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">Informasi Lingkungan (Environment)</h6>
                    <div class="row g-3">
                        <div class="col-6">
                            <span class="text-muted d-block small">Framework:</span>
                            <strong class="text-dark">Laravel {{ app()->version() }}</strong>
                        </div>
                        <div class="col-6">
                            <span class="text-muted d-block small">Database Engine:</span>
                            <strong class="text-dark">MySQL 8.4 (InnoDB)</strong>
                        </div>
                        <div class="col-6">
                            <span class="text-muted d-block small">Server IP Address:</span>
                            <strong class="text-dark">127.0.0.1 (localhost)</strong>
                        </div>
                        <div class="col-6">
                            <span class="text-muted d-block small">Jumlah Shard Aktif:</span>
                            <strong class="text-dark">6 Database Terpisah</strong>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">Parameter Jaringan</h6>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Delay Sinkronisasi Transaksi (ms)</label>
                        <input type="number" class="form-control" value="0" disabled>
                        <div class="form-text"> Latency jaringan antar database cabang ke server pusat.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Retries Koneksi Replikasi</label>
                        <input type="number" class="form-control" value="3" disabled>
                        <div class="form-text">Jumlah percobaan ulang koneksi sebelum ditandai gagal (offline).</div>
                    </div>
                </div>

                <div class="border-top pt-4 text-end">
                    <button class="btn btn-primary fw-semibold px-4" disabled>Simpan Pengaturan</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
