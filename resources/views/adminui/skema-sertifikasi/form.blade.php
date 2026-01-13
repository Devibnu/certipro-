@extends('adminui.layouts.auth')

@section('title', (isset($skema) ? 'Edit' : 'Tambah') . ' Skema Sertifikasi' . (systemCompanyName() ? ' - ' . systemCompanyName() . ' Admin' : ' - Admin'))

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="d-flex align-items-center">
                        <a href="{{ route('adminui.skema-sertifikasi.index') }}" class="btn btn-sm btn-outline-secondary me-3">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                        <div>
                            <h6 class="mb-0">{{ isset($skema) ? 'Edit Skema Sertifikasi' : 'Tambah Skema Sertifikasi' }}</h6>
                            <p class="text-sm mb-0">{{ isset($skema) ? 'Ubah data skema sertifikasi' : 'Isi data skema sertifikasi baru' }}</p>
                        </div>
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

                    <form action="{{ isset($skema) ? route('adminui.skema-sertifikasi.update', $skema) : route('adminui.skema-sertifikasi.store') }}" method="POST">
                        @csrf
                        @if(isset($skema))
                            @method('PUT')
                        @endif

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="kode_skema" class="form-control-label">Kode Skema <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('kode_skema') is-invalid @enderror" 
                                           id="kode_skema" name="kode_skema" 
                                           value="{{ old('kode_skema', $skema->kode_skema ?? '') }}" 
                                           placeholder="Contoh: KKNI-II-A"
                                           required>
                                    @error('kode_skema')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="jenis" class="form-control-label">Jenis Skema <span class="text-danger">*</span></label>
                                    <select class="form-select @error('jenis') is-invalid @enderror" id="jenis" name="jenis" required>
                                        <option value="">Pilih Jenis</option>
                                        @foreach($jenisLabels as $value => $label)
                                            <option value="{{ $value }}" {{ old('jenis', $skema->jenis ?? '') === $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('jenis')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="nama_skema" class="form-control-label">Nama Skema <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nama_skema') is-invalid @enderror" 
                                   id="nama_skema" name="nama_skema" 
                                   value="{{ old('nama_skema', $skema->nama_skema ?? '') }}" 
                                   placeholder="Contoh: Teknisi Komputer"
                                   required>
                            @error('nama_skema')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="deskripsi" class="form-control-label">Deskripsi</label>
                            <textarea class="form-control @error('deskripsi') is-invalid @enderror" 
                                      id="deskripsi" name="deskripsi" rows="3" 
                                      placeholder="Deskripsi singkat tentang skema sertifikasi ini">{{ old('deskripsi', $skema->deskripsi ?? '') }}</textarea>
                            @error('deskripsi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="masa_berlaku" class="form-control-label">Masa Berlaku (tahun)</label>
                                    <input type="number" class="form-control @error('masa_berlaku') is-invalid @enderror" 
                                           id="masa_berlaku" name="masa_berlaku" 
                                           value="{{ old('masa_berlaku', $skema->masa_berlaku ?? '') }}" 
                                           placeholder="Contoh: 3" min="1" max="10">
                                    @error('masa_berlaku')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Kosongkan jika tidak ada batas waktu</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-control-label d-block">Status</label>
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" id="aktif" name="aktif" value="1"
                                               {{ old('aktif', $skema->aktif ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="aktif">Aktif</label>
                                    </div>
                                    <small class="text-muted">Skema nonaktif tidak bisa dipilih saat pendaftaran</small>
                                </div>
                            </div>
                        </div>

                        <hr class="horizontal dark mt-4">

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('adminui.skema-sertifikasi.index') }}" class="btn btn-outline-secondary">
                                Batal
                            </a>
                            <button type="submit" class="btn bg-gradient-primary">
                                <i class="fas fa-save me-1"></i> {{ isset($skema) ? 'Update' : 'Simpan' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Info Panel -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header pb-0">
                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informasi</h6>
                </div>
                <div class="card-body">
                    <p class="text-sm mb-3">
                        <strong>Kode Skema</strong><br>
                        Kode unik untuk mengidentifikasi skema sertifikasi. Contoh: KKNI-II-A, SKM-001
                    </p>
                    <p class="text-sm mb-3">
                        <strong>Jenis Skema</strong><br>
                        <span class="badge bg-gradient-primary">Nasional</span> - Skema standar KKNI<br>
                        <span class="badge bg-gradient-info">Internasional</span> - Skema standar internasional<br>
                        <span class="badge bg-gradient-secondary">Internal</span> - Skema khusus internal
                    </p>
                    <p class="text-sm mb-0">
                        <strong>Masa Berlaku</strong><br>
                        Durasi validitas sertifikat yang diterbitkan (dalam tahun).
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
