@extends('adminui.layouts.auth')

@section('title', 'Edit Bagian Halaman - CMS Landing Page')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="d-flex align-items-center">
                        <a href="{{ route('adminui.bagian-halaman.index', ['halaman_id' => $bagianHalaman->halaman_id]) }}" 
                           class="btn btn-outline-secondary btn-sm me-3">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                        <h6 class="mb-0">Edit Bagian: {{ $bagianHalaman->judul }}</h6>
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

                    <form action="{{ route('adminui.bagian-halaman.update', $bagianHalaman) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="halaman_id" class="form-control-label">Halaman <span class="text-danger">*</span></label>
                                    <select class="form-select @error('halaman_id') is-invalid @enderror" 
                                            id="halaman_id" name="halaman_id" required>
                                        <option value="">-- Pilih Halaman --</option>
                                        @foreach($halamanList as $h)
                                            <option value="{{ $h->id }}" {{ old('halaman_id', $bagianHalaman->halaman_id) == $h->id ? 'selected' : '' }}>
                                                {{ $h->judul }} ({{ $h->slug }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('halaman_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tipe" class="form-control-label">Tipe Bagian <span class="text-danger">*</span></label>
                                    <select class="form-select @error('tipe') is-invalid @enderror" 
                                            id="tipe" name="tipe" required>
                                        <option value="">-- Pilih Tipe --</option>
                                        <option value="hero" {{ old('tipe', $bagianHalaman->tipe) == 'hero' ? 'selected' : '' }}>Hero (Banner utama)</option>
                                        <option value="teks" {{ old('tipe', $bagianHalaman->tipe) == 'teks' ? 'selected' : '' }}>Teks (Paragraf biasa)</option>
                                        <option value="daftar" {{ old('tipe', $bagianHalaman->tipe) == 'daftar' ? 'selected' : '' }}>Daftar (List dengan item)</option>
                                        <option value="faq" {{ old('tipe', $bagianHalaman->tipe) == 'faq' ? 'selected' : '' }}>FAQ (Pertanyaan & Jawaban)</option>
                                    </select>
                                    @error('tipe')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="judul" class="form-control-label">Judul Bagian <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('judul') is-invalid @enderror" 
                                           id="judul" name="judul" value="{{ old('judul', $bagianHalaman->judul) }}" 
                                           placeholder="Masukkan judul bagian" required>
                                    @error('judul')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="urutan" class="form-control-label">Urutan <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('urutan') is-invalid @enderror" 
                                           id="urutan" name="urutan" value="{{ old('urutan', $bagianHalaman->urutan) }}" 
                                           min="0" required>
                                    <small class="text-muted">Angka lebih kecil = tampil lebih atas</small>
                                    @error('urutan')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="isi" class="form-control-label">Isi / Konten</label>
                                    <textarea class="form-control @error('isi') is-invalid @enderror" 
                                              id="isi" name="isi" rows="8" 
                                              placeholder="Masukkan konten bagian ini.">{{ old('isi', $bagianHalaman->isi) }}</textarea>
                                    <small class="text-muted">
                                        Untuk tipe <strong>Hero</strong>: Masukkan deskripsi singkat.<br>
                                        Untuk tipe <strong>Teks</strong>: Masukkan paragraf konten (HTML diperbolehkan).<br>
                                        Untuk tipe <strong>Daftar/FAQ</strong>: Isi ini opsional, item dikelola terpisah.
                                    </small>
                                    @error('isi')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="aktif" name="aktif" value="1" 
                                           {{ old('aktif', $bagianHalaman->aktif) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="aktif">Aktif</label>
                                </div>
                                <small class="text-muted">Bagian yang tidak aktif tidak akan tampil di halaman publik</small>
                            </div>
                        </div>

                        <hr class="horizontal dark my-4">

                        <div class="d-flex justify-content-end">
                            <a href="{{ route('adminui.bagian-halaman.index', ['halaman_id' => $bagianHalaman->halaman_id]) }}" 
                               class="btn btn-outline-secondary me-2">Batal</a>
                            <button type="submit" class="btn bg-gradient-primary">
                                <i class="fas fa-save me-2"></i>Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Info Item Bagian (jika tipe daftar/faq) -->
    @if(in_array($bagianHalaman->tipe, ['daftar', 'faq']))
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <h6>Item Bagian ({{ $bagianHalaman->itemBagianHalaman()->count() }})</h6>
                    <small class="text-muted">Kelola item untuk tipe {{ $bagianHalaman->tipe }}</small>
                </div>
                <div class="card-body">
                    @if($bagianHalaman->itemBagianHalaman()->count() > 0)
                        <ul class="list-group">
                            @foreach($bagianHalaman->itemBagianHalaman()->orderBy('urutan')->get() as $item)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        @if($item->ikon)
                                            <i class="{{ $item->ikon }} me-2"></i>
                                        @endif
                                        <strong>{{ $item->judul }}</strong>
                                        @if($item->deskripsi)
                                            <br><small class="text-muted">{{ Str::limit($item->deskripsi, 100) }}</small>
                                        @endif
                                    </div>
                                    <span class="badge bg-secondary">Urutan: {{ $item->urutan }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted mb-0">Belum ada item untuk bagian ini.</p>
                    @endif
                    <div class="mt-3">
                        <small class="text-info">
                            <i class="fas fa-info-circle me-1"></i>
                            Fitur kelola item bagian akan tersedia di pembaruan berikutnya.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
