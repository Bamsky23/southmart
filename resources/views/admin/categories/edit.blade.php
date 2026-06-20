@extends('layouts.admin')

@section('title', 'Edit Kategori')
@section('header_title', 'Edit Kategori')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                <h5 class="fw-bold m-0 text-dark">
                    <i class="bi bi-pencil-square me-2 text-primary"></i>Edit Kategori
                </h5>
                <p class="text-muted small mb-0 mt-1">
                    Perubahan akan direplikasi ke semua cabang yang sedang online. Kategori ini memiliki
                    <strong class="text-primary">{{ $productCount }} produk</strong>.
                </p>
            </div>

            <div class="card-body p-4">
                @if($errors->any())
                <div class="alert alert-danger border-0 rounded-3 mb-4">
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form action="{{ route('admin.kategori.update', $category->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    {{-- Category ID info --}}
                    <div class="mb-3 p-3 bg-light rounded-3 border d-flex align-items-center gap-3">
                        <div>
                            <div class="text-muted small">ID Kategori</div>
                            <div class="fw-bold fs-5 text-primary">#{{ $category->id }}</div>
                        </div>
                        <div class="vr"></div>
                        <div>
                            <div class="text-muted small">Produk Tertaut</div>
                            <div class="fw-bold fs-5 text-dark">{{ $productCount }}</div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="name" class="form-label fw-semibold small">
                            Nama Kategori <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               name="name"
                               id="name"
                               class="form-control form-control-lg @error('name') is-invalid @enderror"
                               value="{{ old('name', $category->name) }}"
                               required
                               autofocus>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="slug" class="form-label fw-semibold small">
                            Slug URL <span class="text-muted fw-normal">(opsional)</span>
                        </label>
                        <input type="text"
                               name="slug"
                               id="slug"
                               class="form-control @error('slug') is-invalid @enderror"
                               value="{{ old('slug', $category->slug) }}">
                        @error('slug')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Biarkan kosong untuk generate otomatis dari nama.</div>
                    </div>

                    {{-- Live preview --}}
                    <div class="mb-4 p-3 bg-light rounded-3 border">
                        <p class="text-muted small mb-1">Preview:</p>
                        <span class="badge fs-6 px-3 py-2 text-white fw-semibold" id="preview-badge" style="background:#3B82F6">
                            <i class="bi bi-tag-fill me-1"></i>
                            <span id="preview-name">{{ $category->name }}</span>
                        </span>
                    </div>

                    <div class="d-flex justify-content-between border-top pt-4">
                        <a href="{{ route('admin.kategori.index') }}" class="btn btn-outline-secondary fw-semibold">
                            <i class="bi bi-arrow-left me-1"></i> Kembali
                        </a>
                        <button type="submit" class="btn btn-primary fw-semibold px-4">
                            <i class="bi bi-save me-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const nameInput   = document.getElementById('name');
    const slugInput   = document.getElementById('slug');
    const previewName = document.getElementById('preview-name');

    function slugify(text) {
        return text.toString().toLowerCase()
            .replace(/\s+/g, '-')
            .replace(/[^\w\-]+/g, '')
            .replace(/\-\-+/g, '-')
            .trim();
    }

    nameInput.addEventListener('input', function () {
        previewName.textContent = this.value || '{{ $category->name }}';
        slugInput.value = slugify(this.value);
    });

    slugInput.addEventListener('input', function () {
        this.dataset.manual = '1';
    });
</script>
@endsection
