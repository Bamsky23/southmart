@extends('layouts.admin')

@section('title', 'Manajemen Produk')
@section('header_title', 'Master Produk SouthMart')

@section('content')
<!-- Banners & Explanation -->
<div class="alert alert-info border-0 shadow-sm rounded-4 mb-4" role="alert">
    <div class="d-flex align-items-center gap-3">
        <div class="fs-3"><i class="bi bi-info-circle-fill"></i></div>
        <div>
            <h6 class="fw-bold m-0">Replikasi Data Master Otomatis (Master Data Replication)</h6>
            <p class="m-0 small">Sebagai bagian dari arsitektur database terdistribusi, semua tindakan CRUD (Tambah, Edit, Hapus) pada master produk di halaman ini akan langsung disinkronkan dan direplikasikan secara real-time ke database seluruh cabang yang berstatus <strong>ONLINE</strong>.</p>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex flex-wrap justify-content-between align-items-center gap-3">
        <h5 class="fw-bold m-0 text-dark"><i class="bi bi-box-seam-fill me-2 text-primary"></i>Daftar Master Produk</h5>
        <a href="{{ route('admin.produk.create') }}" class="btn btn-primary fw-semibold">
            <i class="bi bi-plus-lg me-1.5"></i> Tambah Produk Baru
        </a>
    </div>
    
    <div class="card-body p-4">
        <!-- Search and Filter Form -->
        <form action="{{ route('admin.produk.index') }}" method="GET" class="row g-3 mb-4">
            <div class="col-md-6 col-lg-4">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Cari barcode, SKU, atau nama..." value="{{ $search }}">
                </div>
            </div>
            <div class="col-md-4 col-lg-3">
                <select name="category_id" class="form-select">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-secondary w-100 fw-semibold">Filter</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Barcode</th>
                        <th>SKU</th>
                        <th>Nama Produk</th>
                        <th>Kategori</th>
                        <th class="text-end">Harga Beli</th>
                        <th class="text-end">Harga Jual</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $prod)
                    <tr>
                        <td class="font-monospace fw-semibold">{{ $prod->barcode }}</td>
                        <td class="font-monospace">{{ $prod->sku }}</td>
                        <td><strong>{{ $prod->name }}</strong></td>
                        <td>
                            <span class="badge bg-light text-dark border">{{ $prod->category->name ?? 'Tanpa Kategori' }}</span>
                        </td>
                        <td class="text-end">Rp {{ number_format($prod->buy_price, 0, ',', '.') }}</td>
                        <td class="text-end text-primary fw-semibold">Rp {{ number_format($prod->sell_price, 0, ',', '.') }}</td>
                        <td class="text-center">
                            <div class="d-inline-flex gap-1.5">
                                <a href="{{ route('admin.produk.edit', $prod->id) }}" class="btn btn-sm btn-outline-primary" title="Ubah">
                                    <i class="bi bi-pencil-fill"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-danger" title="Hapus" onclick="deleteProduct({{ $prod->id }}, '{{ $prod->name }}', this)">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                            <i class="bi bi-search fs-1 d-block mb-3 text-muted-50"></i>
                            Produk tidak ditemukan atau belum ditambahkan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $products->appends(request()->input())->links() }}
        </div>
    </div>
</div>

<script>
function deleteProduct(id, name, btn) {
    // Disable button and show spinner immediately
    btn.disabled = true;
    let originalHtml = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
    
    fetch('/admin/produk/' + id, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            '_method': 'DELETE'
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.text();
    })
    .then(html => {
        // Show success icon
        btn.innerHTML = '<i class="bi bi-check-lg"></i>';
        btn.classList.remove('btn-outline-danger');
        btn.classList.add('btn-success');
        
        // Find the tr element and remove it
        let row = btn.closest('tr');
        row.style.transition = "all 0.5s ease";
        row.style.opacity = "0";
        row.style.transform = "translateX(-20px)";
        setTimeout(() => {
            row.remove();
        }, 500);
    })
    .catch(error => {
        console.error('Error:', error);
        btn.disabled = false;
        btn.innerHTML = originalHtml;
        // Fallback to console error if alert is blocked
        console.log('Terjadi kesalahan saat menghapus produk ' + name);
    });
}
</script>
@endsection
