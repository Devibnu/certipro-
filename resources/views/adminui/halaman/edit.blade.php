@extends('adminui.layouts.auth')

@section('title', 'Edit Halaman - CMS Landing Page')

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
                        <h6 class="mb-0">Edit Halaman: {{ $halaman->judul }}</h6>
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

                    <form action="{{ route('adminui.halaman.update', $halaman) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="judul" class="form-control-label">Judul Halaman <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('judul') is-invalid @enderror" 
                                           id="judul" name="judul" value="{{ old('judul', $halaman->judul) }}" 
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
                                               id="slug" name="slug" value="{{ old('slug', $halaman->slug) }}" 
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
                                           {{ old('aktif', $halaman->aktif) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="aktif">Aktif</label>
                                </div>
                                <small class="text-muted">Halaman yang tidak aktif tidak akan tampil di website</small>
                            </div>
                        </div>

                        <hr class="horizontal dark my-4">

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('adminui.bagian-halaman.index', ['halaman_id' => $halaman->id]) }}" 
                               class="btn btn-outline-info">
                                <i class="fas fa-list me-2"></i>Kelola Bagian ({{ $halaman->bagianHalaman()->count() }})
                            </a>
                            <div>
                                <a href="{{ route('adminui.halaman.index') }}" class="btn btn-outline-secondary me-2">Batal</a>
                                <button type="submit" class="btn bg-gradient-primary">
                                    <i class="fas fa-save me-2"></i>Simpan Perubahan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Preview Section -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h6>Preview URL</h6>
                </div>
                <div class="card-body">
                    <p class="mb-0">
                        <strong>URL Publik:</strong> 
                        <a href="{{ url('/page/' . $halaman->slug) }}" target="_blank" class="text-primary">
                            {{ url('/page/' . $halaman->slug) }}
                            <i class="fas fa-external-link-alt ms-1"></i>
                        </a>
                    </p>
                    @if(!$halaman->aktif)
                        <small class="text-warning">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            Halaman ini tidak aktif dan tidak dapat diakses publik.
                        </small>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
