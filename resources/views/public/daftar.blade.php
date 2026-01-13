@extends('layouts.elearning')

@php $systemName = systemCompanyName(); @endphp
@section('title', 'Daftar Sertifikasi' . ($systemName ? ' - ' . $systemName : ''))
@section('meta_description', 'Daftar sertifikasi kompetensi profesional' . ($systemName ? ' di ' . $systemName : '') . '. Tersedia untuk peserta umum dan kampus.')

@section('content')
<!-- Header Start -->
<div class="container-fluid bg-primary py-5 mb-5 page-header">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10 text-center">
                <h1 class="display-3 text-white animated slideInDown">Daftar Sertifikasi</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-center">
                        <li class="breadcrumb-item"><a class="text-white" href="{{ route('home') }}">Beranda</a></li>
                        <li class="breadcrumb-item text-white active" aria-current="page">Daftar</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>
<!-- Header End -->

<!-- Registration Form Start -->
<div class="container-xxl py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="text-center wow fadeInUp" data-wow-delay="0.1s">
                    <h6 class="section-title bg-white text-center text-primary px-3">Pra-Pendaftaran</h6>
                    <h1 class="mb-5">Formulir Pra-Pendaftaran Sertifikasi</h1>
                </div>
                
                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif

                @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <strong>Terjadi kesalahan:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif

                <div class="bg-light rounded p-4 wow fadeInUp" data-wow-delay="0.3s">
                    <form action="{{ route('daftar.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <!-- Tipe Peserta -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <label class="form-label fw-bold">Tipe Peserta <span class="text-danger">*</span></label>
                                <div class="d-flex gap-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="tipe_peserta" id="tipe_umum" value="umum" {{ old('tipe_peserta', 'umum') === 'umum' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="tipe_umum">
                                            <i class="fas fa-user me-1"></i> Umum
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="tipe_peserta" id="tipe_kampus" value="kampus" {{ old('tipe_peserta') === 'kampus' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="tipe_kampus">
                                            <i class="fas fa-university me-1"></i> Kampus
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Data Pribadi -->
                        <h5 class="mb-3 text-primary"><i class="fas fa-user-circle me-2"></i>Data Pribadi</h5>
                        
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <label for="nama_lengkap" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('nama_lengkap') is-invalid @enderror" id="nama_lengkap" name="nama_lengkap" value="{{ old('nama_lengkap') }}" placeholder="Masukkan nama lengkap sesuai KTP/KTM" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" placeholder="contoh@email.com" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="no_hp" class="form-label">No. HP/WhatsApp <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('no_hp') is-invalid @enderror" id="no_hp" name="no_hp" value="{{ old('no_hp') }}" placeholder="08xxxxxxxxxx" required>
                            </div>
                        </div>

                        <!-- NIK (untuk umum) -->
                        <div class="row g-3 mb-4" id="field_nik">
                            <div class="col-12">
                                <label for="nik" class="form-label">NIK (Nomor Induk Kependudukan) <span class="text-danger" id="nik_required">*</span></label>
                                <input type="text" class="form-control @error('nik') is-invalid @enderror" id="nik" name="nik" value="{{ old('nik') }}" placeholder="16 digit NIK" maxlength="16" pattern="[0-9]{16}" title="NIK harus 16 digit angka">
                                <small class="text-muted">NIK wajib diisi untuk peserta umum</small>
                            </div>
                        </div>

                        <!-- NIM & Institusi (untuk kampus) -->
                        <div class="row g-3 mb-4" id="field_kampus" style="display: none;">
                            <div class="col-md-6">
                                <label for="nim" class="form-label">NIM (Nomor Induk Mahasiswa) <span class="text-danger" id="nim_required" style="display: none;">*</span></label>
                                <input type="text" class="form-control @error('nim') is-invalid @enderror" id="nim" name="nim" value="{{ old('nim') }}" placeholder="Masukkan NIM">
                            </div>
                            <div class="col-md-6">
                                <label for="institusi" class="form-label">Nama Institusi/Kampus</label>
                                <input type="text" class="form-control @error('institusi') is-invalid @enderror" id="institusi" name="institusi" value="{{ old('institusi') }}" placeholder="Nama universitas/kampus">
                            </div>
                        </div>

                        <!-- Upload Identitas -->
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <label for="upload_identitas" class="form-label">Upload Identitas (KTP/KTM)</label>
                                <input type="file" class="form-control @error('upload_identitas') is-invalid @enderror" id="upload_identitas" name="upload_identitas" accept=".jpg,.jpeg,.png,.pdf">
                                <small class="text-muted">Format: JPG, PNG, PDF. Maksimal 2MB (opsional)</small>
                            </div>
                        </div>

                        <!-- Submit -->
                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary py-3 w-100">
                                    <i class="fas fa-paper-plane me-2"></i> Kirim Pendaftaran
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="mt-4 text-center text-muted">
                    <small>
                        <i class="fas fa-info-circle me-1"></i>
                        Setelah pendaftaran terkirim, tim kami akan menghubungi Anda untuk proses selanjutnya.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Registration Form End -->
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tipeUmum = document.getElementById('tipe_umum');
    const tipeKampus = document.getElementById('tipe_kampus');
    const fieldNik = document.getElementById('field_nik');
    const fieldKampus = document.getElementById('field_kampus');
    const nikRequired = document.getElementById('nik_required');
    const nimRequired = document.getElementById('nim_required');
    const nikInput = document.getElementById('nik');
    const nimInput = document.getElementById('nim');

    function toggleFields() {
        if (tipeUmum.checked) {
            fieldNik.style.display = 'flex';
            fieldKampus.style.display = 'none';
            nikRequired.style.display = 'inline';
            nimRequired.style.display = 'none';
            // Clear NIM value dan hapus required
            nimInput.value = '';
            nimInput.removeAttribute('required');
            nikInput.setAttribute('required', 'required');
        } else {
            fieldNik.style.display = 'none';
            fieldKampus.style.display = 'flex';
            nimRequired.style.display = 'inline';
            nikRequired.style.display = 'none';
            // Clear NIK value dan hapus required  
            nikInput.value = '';
            nikInput.removeAttribute('required');
            nimInput.setAttribute('required', 'required');
        }
    }

    tipeUmum.addEventListener('change', toggleFields);
    tipeKampus.addEventListener('change', toggleFields);

    // Initial state
    toggleFields();
});
</script>
@endpush
