@extends('adminui.layouts.auth')

@section('title', (isset($unitKompetensi) ? 'Edit' : 'Tambah') . ' Unit Kompetensi' . (systemCompanyName() ? ' - ' . systemCompanyName() . ' Admin' : ' - Admin'))

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="d-flex align-items-center">
                        <a href="{{ route('adminui.unit-kompetensi.index', request()->only('skema_sertifikasi_id')) }}" class="btn btn-sm btn-outline-secondary me-3">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                        <div>
                            <h6 class="mb-0">{{ isset($unitKompetensi) ? 'Edit Unit Kompetensi' : 'Tambah Unit Kompetensi' }}</h6>
                            <p class="text-sm mb-0">{{ isset($unitKompetensi) ? 'Ubah data unit kompetensi' : 'Isi data unit kompetensi baru' }}</p>
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

                    <form action="{{ isset($unitKompetensi) ? route('adminui.unit-kompetensi.update', $unitKompetensi) : route('adminui.unit-kompetensi.store') }}" method="POST">
                        @csrf
                        @if(isset($unitKompetensi))
                            @method('PUT')
                        @endif

                        <div class="form-group">
                            <label for="skema_sertifikasi_id" class="form-control-label">Skema Sertifikasi <span class="text-danger">*</span></label>
                            <select class="form-select @error('skema_sertifikasi_id') is-invalid @enderror" id="skema_sertifikasi_id" name="skema_sertifikasi_id" required>
                                <option value="">Pilih Skema Sertifikasi</option>
                                @foreach($skemaList as $skema)
                                    <option value="{{ $skema->id }}" 
                                        {{ old('skema_sertifikasi_id', $unitKompetensi->skema_sertifikasi_id ?? ($selectedSkemaId ?? '')) == $skema->id ? 'selected' : '' }}>
                                        {{ $skema->kode_skema }} - {{ $skema->nama_skema }}
                                    </option>
                                @endforeach
                            </select>
                            @error('skema_sertifikasi_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="kode_unit" class="form-control-label">Kode Unit <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('kode_unit') is-invalid @enderror" 
                                           id="kode_unit" name="kode_unit" 
                                           value="{{ old('kode_unit', $unitKompetensi->kode_unit ?? '') }}" 
                                           placeholder="Contoh: TIK.PR01.001.01"
                                           required>
                                    @error('kode_unit')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="nama_unit" class="form-control-label">Nama Unit <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('nama_unit') is-invalid @enderror" 
                                           id="nama_unit" name="nama_unit" 
                                           value="{{ old('nama_unit', $unitKompetensi->nama_unit ?? '') }}" 
                                           placeholder="Contoh: Mengoperasikan Sistem Operasi"
                                           required>
                                    @error('nama_unit')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="deskripsi" class="form-control-label">Deskripsi</label>
                            <textarea class="form-control @error('deskripsi') is-invalid @enderror" 
                                      id="deskripsi" name="deskripsi" rows="3" 
                                      placeholder="Deskripsi singkat tentang unit kompetensi ini">{{ old('deskripsi', $unitKompetensi->deskripsi ?? '') }}</textarea>
                            @error('deskripsi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-control-label d-block">Status</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" id="aktif" name="aktif" value="1"
                                       {{ old('aktif', $unitKompetensi->aktif ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="aktif">Aktif</label>
                            </div>
                            <small class="text-muted">Unit nonaktif tidak akan tampil pada modul asesmen</small>
                        </div>

                        <hr class="horizontal dark mt-4">

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('adminui.unit-kompetensi.index', request()->only('skema_sertifikasi_id')) }}" class="btn btn-outline-secondary">
                                Batal
                            </a>
                            <button type="submit" class="btn bg-gradient-primary">
                                <i class="fas fa-save me-1"></i> {{ isset($unitKompetensi) ? 'Update' : 'Simpan' }}
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
                        <strong>Kode Unit</strong><br>
                        Kode unik berdasarkan SKKNI untuk mengidentifikasi unit kompetensi. 
                        Format: TIK.PR01.001.01
                    </p>
                    <p class="text-sm mb-3">
                        <strong>Skema Sertifikasi</strong><br>
                        Setiap unit kompetensi harus terkait dengan satu skema sertifikasi.
                    </p>
                    <p class="text-sm mb-0">
                        <strong>Status Aktif</strong><br>
                        Hanya unit kompetensi aktif yang akan digunakan pada modul asesmen.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
