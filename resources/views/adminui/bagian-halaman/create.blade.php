@extends('adminui.layouts.auth')

@section('title', 'Tambah Bagian Halaman - CMS Landing Page')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="d-flex align-items-center">
                        <a href="{{ route('adminui.bagian-halaman.index', $halamanId ? ['halaman_id' => $halamanId] : []) }}" 
                           class="btn btn-outline-secondary btn-sm me-3">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                        <h6 class="mb-0">Tambah Bagian Halaman Baru</h6>
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

                    <form action="{{ route('adminui.bagian-halaman.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="halaman_id" class="form-control-label">Halaman <span class="text-danger">*</span></label>
                                    <select class="form-select @error('halaman_id') is-invalid @enderror" 
                                            id="halaman_id" name="halaman_id" required>
                                        <option value="">-- Pilih Halaman --</option>
                                        @foreach($halamanList as $h)
                                            <option value="{{ $h->id }}" {{ old('halaman_id', $halamanId) == $h->id ? 'selected' : '' }}>
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
                                        <option value="hero" {{ old('tipe') == 'hero' ? 'selected' : '' }}>Hero (Banner utama)</option>
                                        <option value="teks" {{ old('tipe') == 'teks' ? 'selected' : '' }}>Teks (Paragraf biasa)</option>
                                        <option value="daftar" {{ old('tipe') == 'daftar' ? 'selected' : '' }}>Daftar (List dengan item)</option>
                                        <option value="faq" {{ old('tipe') == 'faq' ? 'selected' : '' }}>FAQ (Pertanyaan & Jawaban)</option>
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
                                           id="judul" name="judul" value="{{ old('judul') }}" 
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
                                           id="urutan" name="urutan" value="{{ old('urutan', 0) }}" 
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
                                              placeholder="Masukkan konten bagian ini. Untuk tipe 'daftar' dan 'faq', kelola item melalui menu terpisah.">{{ old('isi') }}</textarea>
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

                        {{-- Info untuk tipe Hero --}}
                        <div class="row mt-3" id="heroInfo" style="display: none;">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Untuk Tipe Hero:</strong> Setelah menyimpan bagian ini, Anda dapat menambahkan <strong>slide gambar</strong> (carousel) dengan mengklik tombol <strong>"Edit"</strong> pada bagian yang sudah dibuat.
                                    <br><small class="mt-1 d-block">Fitur upload gambar, tombol CTA, dan multiple slides tersedia di halaman Edit.</small>
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
                                <small class="text-muted">Bagian yang tidak aktif tidak akan tampil di halaman publik</small>
                            </div>
                        </div>

                        <hr class="horizontal dark my-4">

                        <div class="d-flex justify-content-end">
                            <a href="{{ route('adminui.bagian-halaman.index', $halamanId ? ['halaman_id' => $halamanId] : []) }}" 
                               class="btn btn-outline-secondary me-2">Batal</a>
                            <button type="submit" class="btn bg-gradient-primary">
                                <i class="fas fa-save me-2"></i>Simpan Bagian
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
document.getElementById('tipe').addEventListener('change', function() {
    const heroInfo = document.getElementById('heroInfo');
    if (this.value === 'hero') {
        heroInfo.style.display = 'block';
    } else {
        heroInfo.style.display = 'none';
    }
});

// Check on page load
document.addEventListener('DOMContentLoaded', function() {
    const tipe = document.getElementById('tipe').value;
    if (tipe === 'hero') {
        document.getElementById('heroInfo').style.display = 'block';
    }
});
</script>
@endpush
@endsection
