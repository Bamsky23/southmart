@extends('layouts.admin')

@section('title', 'Inventaris & Stok')
@section('header_title', 'Inventaris Ritel Nasional')

@section('content')
<div class="row g-4 mb-4">
    <!-- Main Inventory Table -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                <h5 class="fw-bold m-0 text-dark"><i class="bi bi-archive-fill me-2 text-primary"></i>Status Persediaan Produk di Cabang</h5>
            </div>
            
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Cabang</th>
                                <th>Nama Produk</th>
                                <th>Barcode</th>
                                <th class="text-center">Stok Saat Ini</th>
                                <th class="text-center">Min. Stok</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($inventory as $inv)
                            @php
                                $statusClass = 'bg-success-subtle text-success border-success-subtle';
                                $statusText = 'Stok Aman';
                                
                                if ($inv->stock == 0) {
                                    $statusClass = 'bg-danger-subtle text-danger border-danger-subtle';
                                    $statusText = 'Stok Habis';
                                } elseif ($inv->stock <= $inv->minimum_stock) {
                                    $statusClass = 'bg-warning-subtle text-warning border-warning-subtle';
                                    $statusText = 'Stok Menipis';
                                }
                            @endphp
                            <tr>
                                <td><strong>{{ $inv->branch_name }}</strong></td>
                                <td>
                                    <strong>{{ $inv->product_name }}</strong>
                                    <div class="text-muted small" style="font-size: 0.75rem;">SKU: {{ $inv->sku }}</div>
                                </td>
                                <td class="font-monospace small">{{ $inv->barcode }}</td>
                                <td class="text-center fw-bold fs-6">{{ $inv->stock }}</td>
                                <td class="text-center text-muted">{{ $inv->minimum_stock }}</td>
                                <td class="text-center">
                                    <span class="badge {{ $statusClass }} border rounded-pill">{{ $statusText }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">
                                    <i class="bi bi-boxes fs-1 d-block mb-3 text-muted-50"></i>
                                    Data persediaan stok kosong.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $inventory->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Stock Mutation Control Panel -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                <h5 class="fw-bold m-0 text-dark"><i class="bi bi-arrow-left-right me-2 text-primary"></i>Catat Mutasi / Restok Barang</h5>
                <p class="text-muted small mb-0">Sesuaikan stok fisik pada database node cabang terpilih secara langsung.</p>
            </div>
            
            <div class="card-body p-4">
                <form action="{{ route('admin.inventory.mutation') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="branch_id" class="form-label fw-semibold small">Cabang Tujuan</label>
                        <select name="branch_id" id="branch_id" class="form-select" required>
                            <option value="">Pilih Cabang</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}">{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="product_id" class="form-label fw-semibold small">Produk</label>
                        <select name="product_id" id="product_id" class="form-select" required>
                            <option value="">Pilih Produk</option>
                            @foreach($products as $p)
                                <option value="{{ $p->id }}">{{ $p->name }} (SKU: {{ $p->sku }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small d-block">Tipe Penyesuaian</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="type" id="type_in" value="in" checked>
                            <label class="form-check-label small" for="type_in">Barang Masuk / Restok</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="type" id="type_out" value="out">
                            <label class="form-check-label small" for="type_out">Barang Keluar / Penyesuaian</label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="quantity" class="form-label fw-semibold small">Jumlah Unit</label>
                        <input type="number" name="quantity" id="quantity" class="form-control" min="1" placeholder="Masukkan kuantitas..." required>
                    </div>

                    <div class="mb-4">
                        <label for="reference" class="form-label fw-semibold small">Referensi / Keterangan</label>
                        <input type="text" name="reference" id="reference" class="form-control" placeholder="Contoh: Restok Supplier PT. Indofood" required>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary py-2.5 fw-bold shadow-sm">
                            <i class="bi bi-check-circle me-1.5"></i> Proses Mutasi Stok
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
