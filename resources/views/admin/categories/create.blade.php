@extends('layouts.admin')

@section('title', 'Tambah Kategori')
@section('header_title', 'Tambah Kategori Baru')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                <h5 class="fw-bold m-0 text-dark">
                    <i class="bi bi-plus-circle-fill me-2 text-primary"></i>Tambah Kategori Baru
                </h5>
                <p class="text-muted small mb-0 mt-1">
                    Kategori baru akan otomatis direplikasi ke semua cabang yang sedang online.
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

                <form action="{{ route('admin.kategori.store') }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label for="name" class="form-label fw-semibold small">
                            Nama Kategori <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               name="name"
                               id="name"
                               class="form-control form-control-lg @error('name') is-invalid @enderror"
                               placeholder="Contoh: Peralatan Olahraga"
                               value="{{ old('name') }}"
                               required
                               autofocus>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Nama kategori harus unik dan belum pernah digunakan.</div>
                    </div>

                    <div class="mb-4">
                        <label for="slug" class="form-label fw-semibold small">
                            Slug URL <span class="text-muted fw-normal">(opsional)</span>
                        </label>
                        <input type="text"
                               name="slug"
                               id="slug"
                               class="form-control @error('slug') is-invalid @enderror"
                               placeholder="peralatan-olahraga"
                               value="{{ old('slug') }}">
                        @error('slug')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Biarkan kosong untuk generate otomatis dari nama kategori.</div>
                    </div>

                    {{-- Live preview --}}
                    <div class="mb-4 p-3 bg-light rounded-3 border" id="preview-box" style="display:none">
                        <p class="text-muted small mb-1">Preview kategori:</p>
                        <span class="badge fs-6 px-3 py-2 text-white fw-semibold" id="preview-badge" style="background:#3B82F6">
                            <i class="bi bi-tag-fill me-1"></i>
                            <span id="preview-name">Nama Kategori</span>
                        </span>
                    </div>

                    <div class="d-flex justify-content-end gap-2 border-top pt-4">
                        <a href="{{ route('admin.kategori.index') }}" class="btn btn-outline-secondary fw-semibold">
                            Batal
                        </a>
                        <button type="submit" class="btn btn-primary fw-semibold px-4">
                            <i class="bi bi-save me-1"></i> Simpan & Replikasi
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
    const previewBox  = document.getElementById('preview-box');
    const previewName = document.getElementById('preview-name');

    function slugify(text) {
        return text.toString().toLowerCase()
            .replace(/\s+/g, '-')
            .replace(/[^\w\-]+/g, '')
            .replace(/\-\-+/g, '-')
            .trim();
    }

    nameInput.addEventListener('input', function () {
        const val = this.value.trim();
        if (val) {
            previewBox.style.display = 'block';
            previewName.textContent  = val;
            if (!slugInput.dataset.manual) {
                slugInput.value = slugify(val);
            }
        } else {
            previewBox.style.display = 'none';
        }
    });

    slugInput.addEventListener('input', function () {
        this.dataset.manual = '1';
    });
</script>
@endsection
