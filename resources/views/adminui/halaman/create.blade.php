@extends('adminui.layouts.auth')

@section('title', 'Tambah Halaman - CMS Landing Page')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="d-flex align-items-center">
                        <a href="{{ route('adminui.halaman.index') }}" class="btn btn-outline-secondary btn-sm me-3">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                        <h6 class="mb-0">Tambah Halaman Baru</h6>
                    </div>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('adminui.halaman.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="judul" class="form-control-label">Judul Halaman <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('judul') is-invalid @enderror" 
                                           id="judul" name="judul" value="{{ old('judul') }}" 
                                           placeholder="Masukkan judul halaman" required>
                                    @error('judul')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="slug" class="form-control-label">Slug URL <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">/page/</span>
                                        <input type="text" class="form-control @error('slug') is-invalid @enderror" 
                                               id="slug" name="slug" value="{{ old('slug') }}" 
                                               placeholder="contoh: tentang" required>
                                    </div>
                                    <small class="text-muted">Gunakan huruf kecil tanpa spasi. Contoh: home, tentang, skema, alur, persyaratan, kontak</small>
                                    @error('slug')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="aktif" name="aktif" value="1" 
                                           {{ old('aktif', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="aktif">Aktif</label>
                                </div>
                                <small class="text-muted">Halaman yang tidak aktif tidak akan tampil di website</small>
                            </div>
                        </div>

                        <hr class="horizontal dark my-4">

                        <div class="d-flex justify-content-end">
                            <a href="{{ route('adminui.halaman.index') }}" class="btn btn-outline-secondary me-2">Batal</a>
                            <button type="submit" class="btn bg-gradient-primary">
                                <i class="fas fa-save me-2"></i>Simpan Halaman
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('judul').addEventListener('keyup', function() {
    let slug = this.value
        .toLowerCase()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .trim();
    document.getElementById('slug').value = slug;
});
</script>
@endpush
@endsection
