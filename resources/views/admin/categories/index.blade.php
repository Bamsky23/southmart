@extends('layouts.admin')

@section('title', 'Manajemen Kategori')
@section('header_title', 'Master Kategori Produk')

@section('content')
<div class="alert alert-info border-0 shadow-sm rounded-4 mb-4" role="alert">
    <div class="d-flex align-items-center gap-3">
        <div class="fs-3"><i class="bi bi-info-circle-fill"></i></div>
        <div>
            <h6 class="fw-bold m-0">Replikasi Kategori Otomatis</h6>
            <p class="m-0 small">Semua tindakan CRUD pada kategori akan langsung direplikasi ke database seluruh cabang yang berstatus <strong>ONLINE</strong>. Kategori yang memiliki produk tidak dapat dihapus.</p>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold m-0 text-dark">
            <i class="bi bi-tags-fill me-2 text-primary"></i>Daftar Kategori
        </h5>
        <a href="{{ route('admin.kategori.create') }}" class="btn btn-primary fw-semibold">
            <i class="bi bi-plus-lg me-1"></i> Tambah Kategori
        </a>
    </div>

    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width:60px">ID</th>
                        <th>Nama Kategori</th>
                        <th>Slug</th>
                        <th class="text-center">Jumlah Produk</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $cat)
                    <tr>
                        <td class="fw-semibold text-muted">{{ $cat->id }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge rounded-pill text-white fw-semibold px-3 py-1"
                                    style="background: {{ ['#10B981','#3B82F6','#F59E0B','#EC4899','#6B7280','#EF4444','#8B5CF6','#06B6D4','#84CC16','#F97316','#14B8A6'][($cat->id - 1) % 11] ?? '#64748B' }}">
                                    {{ $cat->name }}
                                </span>
                            </div>
                        </td>
                        <td class="font-monospace text-muted small">{{ $cat->slug }}</td>
                        <td class="text-center">
                            @if($cat->products_count > 0)
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                    {{ $cat->products_count }} produk
                                </span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border">
                                    0 produk
                                </span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="d-inline-flex gap-1">
                                <a href="{{ route('admin.kategori.edit', $cat->id) }}"
                                   class="btn btn-sm btn-outline-primary" title="Edit Kategori">
                                    <i class="bi bi-pencil-fill"></i>
                                </a>
                                @if($cat->products_count == 0)
                                <form action="{{ route('admin.kategori.destroy', $cat->id) }}" method="POST"
                                      onsubmit="return confirm('Hapus kategori \'{{ $cat->name }}\'? Tindakan ini tidak dapat dibatalkan.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus Kategori">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </form>
                                @else
                                <button class="btn btn-sm btn-outline-secondary" disabled title="Tidak dapat dihapus — masih ada produk">
                                    <i class="bi bi-lock-fill"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-5">
                            <i class="bi bi-tags fs-1 d-block mb-3 opacity-25"></i>
                            Belum ada kategori. Tambahkan kategori pertama.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
