@extends('layouts.admin')

@section('title', 'Ubah Produk')
@section('header_title', 'Ubah Master Produk')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                <h5 class="fw-bold m-0 text-dark"><i class="bi bi-pencil-fill me-2 text-primary"></i>Ubah Produk: {{ $product->name }}</h5>
                <p class="text-muted small mb-0">Ubah informasi produk. Perubahan data master akan otomatis direplikasi ke cabang online.</p>
            </div>
            
            <div class="card-body p-4">
                <form action="{{ route('admin.produk.update', $product->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="barcode" class="form-label fw-semibold small">Kode Barcode</label>
                            <input type="text" name="barcode" id="barcode" class="form-control @error('barcode') is-invalid @enderror" placeholder="Contoh: 89686011030" value="{{ old('barcode', $product->barcode) }}" required>
                            @error('barcode')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="sku" class="form-label fw-semibold small">SKU Produk</label>
                            <input type="text" name="sku" id="sku" class="form-control @error('sku') is-invalid @enderror" placeholder="Contoh: IDM-GRG" value="{{ old('sku', $product->sku) }}" required>
                            @error('sku')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold small">Nama Produk</label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" placeholder="Contoh: Indomie Goreng Spesial" value="{{ old('name', $product->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="image_url" class="form-label fw-semibold small">URL Gambar Produk</label>
                        <input type="text" name="image_url" id="image_url" class="form-control @error('image_url') is-invalid @enderror" placeholder="Contoh: https://images.unsplash.com/photo-xxx" value="{{ old('image_url', $product->image_url) }}">
                        @error('image_url')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3 col-md-6">
                        <label for="category_id" class="form-label fw-semibold small">Kategori</label>
                        <select name="category_id" id="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                            <option value="">Pilih Kategori</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="buy_price" class="form-label fw-semibold small">Harga Beli (Rp)</label>
                            <input type="number" name="buy_price" id="buy_price" class="form-control @error('buy_price') is-invalid @enderror" placeholder="Harga beli modal" value="{{ old('buy_price', $product->buy_price) }}" min="0" required>
                            @error('buy_price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="sell_price" class="form-label fw-semibold small">Harga Jual (Rp)</label>
                            <input type="number" name="sell_price" id="sell_price" class="form-control @error('sell_price') is-invalid @enderror" placeholder="Harga jual eceran" value="{{ old('sell_price', $product->sell_price) }}" min="0" required>
                            @error('sell_price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Distributed Stocks Adjustments -->
                    <div class="border-top pt-4 mb-4">
                        <h6 class="fw-bold text-dark mb-3"><i class="bi bi-boxes text-primary me-2"></i>Sesuaikan Stok di Masing-Masing Cabang (Horizontal Shards)</h6>
                        <div class="row g-3">
                            @foreach($branches as $b)
                            @php
                                $branchStock = $stocks[$b->id] ?? 0;
                            @endphp
                            <div class="col-md-4 col-sm-6">
                                <label for="stocks_{{ $b->id }}" class="form-label fw-semibold small text-muted">{{ $b->name }}</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted small" style="font-size: 0.75rem;">Stok:</span>
                                    <input type="number" name="stocks[{{ $b->id }}]" id="stocks_{{ $b->id }}" class="form-control fw-medium" value="{{ old('stocks.'.$b->id, $branchStock) }}" min="0">
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <div class="form-text mt-2">Kolom di atas merujuk pada baris tabel <code>inventory</code> terfragmentasi di database masing-masing cabang.</div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 border-top pt-4">
                        <a href="{{ route('admin.produk.index') }}" class="btn btn-outline-secondary fw-semibold">Batal</a>
                        <button type="submit" class="btn btn-primary fw-semibold px-4">
                            <i class="bi bi-save me-1.5"></i> Simpan Perubahan & Replikasi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
