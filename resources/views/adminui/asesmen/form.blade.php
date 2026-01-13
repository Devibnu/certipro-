@extends('adminui.layouts.auth')

@section('title', 'Mulai Asesmen' . (systemCompanyName() ? ' - ' . systemCompanyName() . ' Admin' : ' - Admin'))

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <!-- Info Pendaftaran -->
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Form Asesmen</h6>
                            <p class="text-sm mb-0">Penilaian kompetensi per Kriteria Unjuk Kerja (KUK)</p>
                        </div>
                        <a href="{{ route('adminui.asesmen.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td class="text-secondary" width="150">No. Pendaftaran</td>
                                    <td><strong>{{ $pendaftaran->nomor_pendaftaran }}</strong></td>
                                </tr>
                                <tr>
                                    <td class="text-secondary">Nama Asesi</td>
                                    <td><strong>{{ $pendaftaran->user->name ?? $pendaftaran->nama_lengkap ?? '-' }}</strong></td>
                                </tr>
                                <tr>
                                    <td class="text-secondary">Email</td>
                                    <td>{{ $pendaftaran->user->email ?? $pendaftaran->email ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td class="text-secondary" width="150">Skema</td>
                                    <td>
                                        <span class="badge bg-gradient-info">{{ $pendaftaran->skemaSertifikasi->kode_skema ?? '-' }}</span>
                                        {{ $pendaftaran->skemaSertifikasi->nama_skema ?? '-' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-secondary">Tanggal Daftar</td>
                                    <td>{{ $pendaftaran->tanggal_daftar->format('d F Y') }}</td>
                                </tr>
                                <tr>
                                    <td class="text-secondary">Status</td>
                                    <td>
                                        <span class="badge badge-sm {{ $pendaftaran->status_badge }}">
                                            {{ $pendaftaran->status_label }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Asesmen -->
            <form action="{{ route('adminui.asesmen.simpan', $pendaftaran->id) }}" method="POST" id="formAsesmen">
                @csrf

                <!-- Metode Asesmen -->
                <div class="card mb-4">
                    <div class="card-header pb-0">
                        <h6 class="mb-0">Metode Asesmen</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Metode Asesmen <span class="text-danger">*</span></label>
                                    <select name="metode_asesmen" class="form-select @error('metode_asesmen') is-invalid @enderror" required>
                                        <option value="">-- Pilih Metode --</option>
                                        <option value="observasi" {{ old('metode_asesmen') == 'observasi' ? 'selected' : '' }}>Observasi Langsung</option>
                                        <option value="portofolio" {{ old('metode_asesmen') == 'portofolio' ? 'selected' : '' }}>Portofolio</option>
                                        <option value="wawancara" {{ old('metode_asesmen') == 'wawancara' ? 'selected' : '' }}>Wawancara</option>
                                    </select>
                                    @error('metode_asesmen')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Catatan Asesor (Opsional)</label>
                                    <textarea name="catatan_asesor" class="form-control @error('catatan_asesor') is-invalid @enderror" 
                                              rows="3" placeholder="Catatan umum mengenai asesmen...">{{ old('catatan_asesor') }}</textarea>
                                    @error('catatan_asesor')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Penilaian per Unit Kompetensi -->
                <div class="card mb-4">
                    <div class="card-header pb-0">
                        <h6 class="mb-0">Penilaian Kompetensi</h6>
                        <p class="text-sm text-secondary mb-0">Klik pada setiap unit untuk melihat dan menilai KUK</p>
                    </div>
                    <div class="card-body">
                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible" role="alert">
                                <span class="text-white">{{ session('error') }}</span>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @error('hasil')
                            <div class="alert alert-danger" role="alert">
                                <span class="text-white">{{ $message }}</span>
                            </div>
                        @enderror

                        <div class="accordion" id="accordionUnits">
                            @foreach($unitKompetensi as $index => $unit)
                            <div class="accordion-item border mb-3 rounded">
                                <h2 class="accordion-header" id="heading{{ $unit->id }}">
                                    <button class="accordion-button {{ $index > 0 ? 'collapsed' : '' }}" type="button" 
                                            data-bs-toggle="collapse" data-bs-target="#collapse{{ $unit->id }}" 
                                            aria-expanded="{{ $index == 0 ? 'true' : 'false' }}" aria-controls="collapse{{ $unit->id }}">
                                        <div>
                                            <span class="badge bg-gradient-primary me-2">{{ $unit->kode_unit }}</span>
                                            <strong>{{ $unit->nama_unit }}</strong>
                                            <span class="badge bg-gradient-secondary ms-2">{{ $unit->kuks->count() }} KUK</span>
                                        </div>
                                    </button>
                                </h2>
                                <div id="collapse{{ $unit->id }}" class="accordion-collapse collapse {{ $index == 0 ? 'show' : '' }}" 
                                     aria-labelledby="heading{{ $unit->id }}" data-bs-parent="#accordionUnits">
                                    <div class="accordion-body p-0">
                                        @if($unit->kuks->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-hover align-middle mb-0">
                                                <thead class="bg-gray-100">
                                                    <tr>
                                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder ps-3" style="width: 100px;">Kode</th>
                                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder">Kriteria Unjuk Kerja</th>
                                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder text-center" style="width: 220px;">Penilaian <span class="text-danger">*</span></th>
                                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder pe-3" style="width: 280px;">Catatan Asesor</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($unit->kuks as $kukIndex => $kuk)
                                                    <tr>
                                                        <td class="ps-3">
                                                            <span class="badge bg-gradient-info">{{ $kuk->kode_kuk }}</span>
                                                        </td>
                                                        <td>
                                                            <p class="text-sm text-dark mb-0">{{ $kuk->pernyataan_unjuk_kerja }}</p>
                                                        </td>
                                                        <td class="text-center">
                                                            <div class="btn-group" role="group">
                                                                <input type="radio" class="btn-check" 
                                                                       name="hasil[{{ $kuk->id }}]" 
                                                                       id="kompeten_{{ $kuk->id }}" 
                                                                       value="kompeten"
                                                                       {{ old("hasil.{$kuk->id}") == 'kompeten' ? 'checked' : '' }}
                                                                       required>
                                                                <label class="btn btn-outline-success btn-sm px-3" for="kompeten_{{ $kuk->id }}">
                                                                    <i class="fas fa-check me-1"></i> K
                                                                </label>
                                                                <input type="radio" class="btn-check" 
                                                                       name="hasil[{{ $kuk->id }}]" 
                                                                       id="belum_kompeten_{{ $kuk->id }}" 
                                                                       value="belum_kompeten"
                                                                       {{ old("hasil.{$kuk->id}") == 'belum_kompeten' ? 'checked' : '' }}>
                                                                <label class="btn btn-outline-danger btn-sm px-3" for="belum_kompeten_{{ $kuk->id }}">
                                                                    <i class="fas fa-times me-1"></i> BK
                                                                </label>
                                                            </div>
                                                        </td>
                                                        <td class="pe-3">
                                                            <textarea name="catatan[{{ $kuk->id }}]" 
                                                                      class="form-control form-control-sm" 
                                                                      rows="2"
                                                                      placeholder="Catatan asesor...">{{ old("catatan.{$kuk->id}") }}</textarea>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        @else
                                        <div class="p-4 text-center">
                                            <i class="fas fa-info-circle text-secondary fa-2x mb-2"></i>
                                            <p class="text-sm text-secondary mb-0">Tidak ada KUK untuk unit ini.</p>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        @if($unitKompetensi->isEmpty())
                        <div class="text-center py-4">
                            <i class="fas fa-exclamation-triangle text-warning fa-3x mb-3"></i>
                            <p class="text-sm">Tidak ada unit kompetensi dalam skema ini.</p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Submit Button -->
                @if($unitKompetensi->isNotEmpty())
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-sm text-secondary mb-0">
                                    <i class="fas fa-info-circle me-1"></i>
                                    <strong>K</strong> = Kompeten, <strong>BK</strong> = Belum Kompeten
                                </p>
                                <p class="text-xs text-secondary mb-0">
                                    Jika ada 1 KUK = Belum Kompeten, maka hasil asesmen = Belum Kompeten
                                </p>
                            </div>
                            <div>
                                <a href="{{ route('adminui.asesmen.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i> Batal
                                </a>
                                <button type="submit" class="btn bg-gradient-success">
                                    <i class="fas fa-save me-1"></i> Simpan Asesmen
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formAsesmen');
    const submitBtn = form.querySelector('button[type="submit"]');
    
    submitBtn.addEventListener('click', function(e) {
        e.preventDefault();
        
        // Check if all KUK have been assessed
        const radioGroups = document.querySelectorAll('input[type="radio"][name^="hasil"]');
        const groupNames = [...new Set([...radioGroups].map(r => r.name))];
        
        let allAssessed = true;
        let unassessedCount = 0;
        
        for (const groupName of groupNames) {
            const checked = document.querySelector(`input[name="${groupName}"]:checked`);
            if (!checked) {
                allAssessed = false;
                unassessedCount++;
            }
        }
        
        if (!allAssessed) {
            Swal.fire({
                icon: 'warning',
                title: 'Penilaian Belum Lengkap',
                html: `Masih ada <strong>${unassessedCount}</strong> KUK yang belum dinilai.<br>Mohon lengkapi semua penilaian terlebih dahulu.`,
                confirmButtonColor: '#5e72e4'
            });
            return;
        }
        
        // Count kompeten and belum kompeten
        let kompetenCount = 0;
        let belumKompetenCount = 0;
        
        for (const groupName of groupNames) {
            const checked = document.querySelector(`input[name="${groupName}"]:checked`);
            if (checked.value === 'kompeten') {
                kompetenCount++;
            } else {
                belumKompetenCount++;
            }
        }
        
        const totalKuk = groupNames.length;
        
        const hasilBadge = belumKompetenCount > 0 
            ? '<span style="background: linear-gradient(310deg, #ea0606 0%, #ff667c 100%); color: white; padding: 8px 16px; border-radius: 8px; font-weight: 600; font-size: 14px;">❌ BELUM KOMPETEN</span>'
            : '<span style="background: linear-gradient(310deg, #2dce89 0%, #26c6da 100%); color: white; padding: 8px 16px; border-radius: 8px; font-weight: 600; font-size: 14px;">✅ KOMPETEN</span>';
        
        Swal.fire({
            title: '<strong>Konfirmasi Simpan Asesmen</strong>',
            html: `
                <div style="text-align: left; padding: 10px 0;">
                    <p style="margin-bottom: 15px; color: #555; font-size: 14px;">Apakah Anda yakin ingin menyimpan hasil asesmen ini?</p>
                    
                    <div style="background: #f8f9fa; border-radius: 10px; padding: 15px; margin-bottom: 15px;">
                        <p style="margin: 0 0 12px 0; font-weight: 600; color: #344767; font-size: 13px;">📋 Ringkasan Penilaian:</p>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                            <span style="color: #666;">Total KUK</span>
                            <span style="font-weight: 600; color: #344767;">${totalKuk} item</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                            <span style="color: #2dce89;">✓ Kompeten</span>
                            <span style="font-weight: 600; color: #2dce89;">${kompetenCount} item</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0;">
                            <span style="color: #ea0606;">✗ Belum Kompeten</span>
                            <span style="font-weight: 600; color: #ea0606;">${belumKompetenCount} item</span>
                        </div>
                    </div>
                    
                    <div style="background: ${belumKompetenCount > 0 ? '#ffeef0' : '#e6f9f0'}; border-radius: 10px; padding: 15px; text-align: center; margin-bottom: 15px;">
                        <p style="margin: 0 0 10px 0; font-size: 12px; color: #666;">Hasil Asesmen:</p>
                        ${hasilBadge}
                    </div>
                    
                    <div style="background: #fff3cd; border-radius: 8px; padding: 12px; border-left: 4px solid #ffc107;">
                        <p style="margin: 0; font-size: 12px; color: #856404;">
                            <strong>⚠️ Perhatian:</strong> Data asesmen tidak dapat diubah setelah disimpan.
                        </p>
                    </div>
                </div>
            `,
            icon: 'question',
            iconColor: '#5e72e4',
            showCancelButton: true,
            confirmButtonColor: '#2dce89',
            cancelButtonColor: '#f5365c',
            confirmButtonText: '✓ Ya, Simpan Asesmen',
            cancelButtonText: '✗ Batal',
            reverseButtons: true,
            width: 480,
            customClass: {
                popup: 'swal-wide-asesmen',
                title: 'swal-title-custom',
                confirmButton: 'swal-confirm-btn',
                cancelButton: 'swal-cancel-btn'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Menyimpan...',
                    text: 'Mohon tunggu sebentar',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                form.submit();
            }
        });
    });
});
</script>
@endpush

@push('styles')
<style>
/* Asesmen Form Table Styles */
.accordion-body .table th {
    border-bottom: 2px solid #e9ecef;
    padding: 12px 8px;
    white-space: nowrap;
}
.accordion-body .table td {
    padding: 12px 8px;
    vertical-align: middle;
}
.accordion-body .table tbody tr:hover {
    background-color: #f8f9fa;
}
.accordion-body .table .btn-group .btn {
    padding: 6px 12px;
    font-size: 12px;
}
.accordion-body .table textarea.form-control {
    font-size: 13px;
    min-height: 60px;
}
.accordion-button:not(.collapsed) {
    background-color: #f0f2f5;
}

/* SweetAlert Custom Styles */
.swal-wide-asesmen {
    border-radius: 15px !important;
}
.swal-title-custom {
    font-size: 18px !important;
    color: #344767 !important;
}
.swal-confirm-btn {
    font-weight: 600 !important;
    padding: 10px 24px !important;
    border-radius: 8px !important;
}
.swal-cancel-btn {
    font-weight: 600 !important;
    padding: 10px 24px !important;
    border-radius: 8px !important;
}
</style>
@endpush
