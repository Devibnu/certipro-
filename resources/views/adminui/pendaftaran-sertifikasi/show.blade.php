@extends('adminui.layouts.auth')

@section('title', 'Detail Pendaftaran' . (systemCompanyName() ? ' - ' . systemCompanyName() . ' Admin' : ' - Admin'))

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="d-flex align-items-center">
                        <a href="{{ route('adminui.pendaftaran-sertifikasi.index') }}" class="btn btn-sm btn-outline-secondary me-3">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                        <div>
                            <h6 class="mb-0">Detail Pendaftaran</h6>
                            <p class="text-sm mb-0">{{ $pendaftaran->nomor_pendaftaran }}</p>
                        </div>
                        <div class="ms-auto d-flex align-items-center gap-2">
                            <a href="{{ route('adminui.pendaftaran-sertifikasi.audit-pdf', $pendaftaran->id) }}" 
                               class="btn btn-sm btn-outline-primary" 
                               title="Download Audit Evidence PDF"
                               target="_blank">
                                <i class="fas fa-file-pdf me-1"></i> Audit PDF
                            </a>
                            <span class="badge badge-lg {{ $pendaftaran->status_badge }}">
                                {{ $pendaftaran->status_label }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <!-- Data Asesi -->
                    <h6 class="text-uppercase text-sm font-weight-bolder opacity-6 mb-3">Data Asesi</h6>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <p class="text-sm mb-1"><strong>Nama Lengkap:</strong></p>
                            <p class="text-sm">{{ $pendaftaran->user->name ?? $pendaftaran->nama_lengkap ?? '-' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="text-sm mb-1"><strong>Email:</strong></p>
                            <p class="text-sm">{{ $pendaftaran->user->email ?? $pendaftaran->email ?? '-' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="text-sm mb-1"><strong>No. Telepon:</strong></p>
                            <p class="text-sm">{{ $pendaftaran->user->phone ?? $pendaftaran->no_hp ?? '-' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="text-sm mb-1"><strong>Tipe Peserta:</strong></p>
                            <p class="text-sm">
                                @if($pendaftaran->tipe_peserta)
                                    <span class="badge bg-{{ $pendaftaran->tipe_peserta === 'umum' ? 'info' : 'warning' }}">{{ ucfirst($pendaftaran->tipe_peserta) }}</span>
                                @else
                                    {{ $pendaftaran->user->location ?? '-' }}
                                @endif
                            </p>
                        </div>
                        @if($pendaftaran->nik)
                        <div class="col-md-6">
                            <p class="text-sm mb-1"><strong>NIK:</strong></p>
                            <p class="text-sm">{{ $pendaftaran->nik }}</p>
                        </div>
                        @endif
                        @if($pendaftaran->nim)
                        <div class="col-md-6">
                            <p class="text-sm mb-1"><strong>NIM:</strong></p>
                            <p class="text-sm">{{ $pendaftaran->nim }}</p>
                        </div>
                        @endif
                        @if($pendaftaran->institusi)
                        <div class="col-md-6">
                            <p class="text-sm mb-1"><strong>Institusi:</strong></p>
                            <p class="text-sm">{{ $pendaftaran->institusi }}</p>
                        </div>
                        @endif
                    </div>

                    <hr class="horizontal dark">

                    <!-- Data Pendaftaran -->
                    <h6 class="text-uppercase text-sm font-weight-bolder opacity-6 mb-3">Data Pendaftaran</h6>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <p class="text-sm mb-1"><strong>Nomor Pendaftaran:</strong></p>
                            <p class="text-sm font-weight-bold">{{ $pendaftaran->nomor_pendaftaran }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="text-sm mb-1"><strong>Tanggal Daftar:</strong></p>
                            <p class="text-sm">{{ $pendaftaran->tanggal_daftar->format('d F Y') }}</p>
                        </div>
                    </div>

                    <hr class="horizontal dark">

                    <!-- Skema Sertifikasi -->
                    <h6 class="text-uppercase text-sm font-weight-bolder opacity-6 mb-3">Skema Sertifikasi</h6>
                    @if($pendaftaran->skemaSertifikasi)
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <p class="text-sm mb-1"><strong>Kode Skema:</strong></p>
                            <p class="text-sm">
                                <span class="badge badge-sm bg-gradient-info">{{ $pendaftaran->skemaSertifikasi->kode_skema }}</span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="text-sm mb-1"><strong>Nama Skema:</strong></p>
                            <p class="text-sm font-weight-bold">{{ $pendaftaran->skemaSertifikasi->nama_skema }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="text-sm mb-1"><strong>Jenis:</strong></p>
                            <p class="text-sm">{{ $pendaftaran->skemaSertifikasi->jenis_label }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="text-sm mb-1"><strong>Masa Berlaku:</strong></p>
                            <p class="text-sm">{{ $pendaftaran->skemaSertifikasi->masa_berlaku ? $pendaftaran->skemaSertifikasi->masa_berlaku . ' tahun' : '-' }}</p>
                        </div>
                    </div>

                    <!-- Unit Kompetensi dalam Skema -->
                    @if($pendaftaran->skemaSertifikasi->unitKompetensi->count() > 0)
                        <h6 class="text-uppercase text-xs font-weight-bolder opacity-6 mb-2">Unit Kompetensi:</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder">Kode</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder">Nama Unit</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pendaftaran->skemaSertifikasi->unitKompetensi as $unit)
                                        <tr>
                                            <td class="text-xs">{{ $unit->kode_unit }}</td>
                                            <td class="text-xs">{{ $unit->nama_unit }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                    @else
                    <div class="alert alert-warning mb-3" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <span class="text-white">Skema sertifikasi belum dipilih. Silakan pilih skema untuk melanjutkan proses pendaftaran.</span>
                    </div>
                    
                    <!-- Form Pilih Skema -->
                    <div class="card card-body border card-plain border-radius-lg">
                        <h6 class="text-sm font-weight-bold mb-3">
                            <i class="fas fa-certificate me-2 text-info"></i>Pilih Skema Sertifikasi
                        </h6>
                        <form id="formAssignSkema" action="{{ route('adminui.pendaftaran-sertifikasi.assign-skema', $pendaftaran->id) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="skema_sertifikasi_id" class="form-label">Skema Sertifikasi <span class="text-danger">*</span></label>
                                <select name="skema_sertifikasi_id" id="skema_sertifikasi_id" class="form-select @error('skema_sertifikasi_id') is-invalid @enderror" required>
                                    <option value="">-- Pilih Skema Sertifikasi --</option>
                                    @foreach($skemaList as $skema)
                                        <option value="{{ $skema->id }}" {{ old('skema_sertifikasi_id') == $skema->id ? 'selected' : '' }}>
                                            {{ $skema->kode_skema }} - {{ $skema->nama_skema }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('skema_sertifikasi_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Hanya skema dengan status aktif yang ditampilkan</small>
                            </div>
                            <button type="button" class="btn bg-gradient-primary" id="btnSimpanSkema">
                                <i class="fas fa-save me-2"></i>Simpan Skema
                            </button>
                        </form>
                    </div>
                    @endif

                    @if($pendaftaran->catatan_admin)
                        <hr class="horizontal dark">
                        <h6 class="text-uppercase text-sm font-weight-bolder opacity-6 mb-3">Catatan Admin</h6>
                        <div class="alert alert-secondary">
                            <p class="text-sm mb-0">{{ $pendaftaran->catatan_admin }}</p>
                        </div>
                    @endif

                    <!-- Action Buttons -->
                    @if($pendaftaran->isDiajukan())
                        <hr class="horizontal dark mt-4">
                        <div class="d-flex gap-2">
                            <button type="button" class="btn bg-gradient-success" data-bs-toggle="modal" data-bs-target="#verifikasiModal">
                                <i class="fas fa-check me-1"></i> Verifikasi
                            </button>
                            <button type="button" class="btn bg-gradient-danger" data-bs-toggle="modal" data-bs-target="#tolakModal">
                                <i class="fas fa-times me-1"></i> Tolak
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Info Panel -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header pb-0">
                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Status Pendaftaran</h6>
                </div>
                <div class="card-body">
                    <div class="timeline timeline-one-side">
                        <div class="timeline-block mb-3">
                            <span class="timeline-step bg-success">
                                <i class="fas fa-check text-white"></i>
                            </span>
                            <div class="timeline-content">
                                <h6 class="text-dark text-sm font-weight-bold mb-0">Diajukan</h6>
                                <p class="text-secondary font-weight-bold text-xs mt-1 mb-0">
                                    {{ $pendaftaran->created_at->format('d M Y H:i') }}
                                </p>
                            </div>
                        </div>
                        
                        @if($pendaftaran->isDiverifikasi() || $pendaftaran->isSiapAsesmen())
                            <div class="timeline-block mb-3">
                                <span class="timeline-step bg-info">
                                    <i class="fas fa-clipboard-check text-white"></i>
                                </span>
                                <div class="timeline-content">
                                    <h6 class="text-dark text-sm font-weight-bold mb-0">Diverifikasi</h6>
                                    <p class="text-secondary font-weight-bold text-xs mt-1 mb-0">
                                        {{ $pendaftaran->updated_at->format('d M Y H:i') }}
                                    </p>
                                </div>
                            </div>
                        @elseif($pendaftaran->isDitolak())
                            <div class="timeline-block mb-3">
                                <span class="timeline-step bg-danger">
                                    <i class="fas fa-times text-white"></i>
                                </span>
                                <div class="timeline-content">
                                    <h6 class="text-dark text-sm font-weight-bold mb-0">Ditolak</h6>
                                    <p class="text-secondary font-weight-bold text-xs mt-1 mb-0">
                                        {{ $pendaftaran->updated_at->format('d M Y H:i') }}
                                    </p>
                                </div>
                            </div>
                        @elseif($pendaftaran->isDiajukan())
                            <div class="timeline-block mb-3">
                                <span class="timeline-step bg-warning">
                                    <i class="fas fa-clock text-white"></i>
                                </span>
                                <div class="timeline-content">
                                    <h6 class="text-dark text-sm font-weight-bold mb-0">Menunggu Verifikasi</h6>
                                    <p class="text-secondary font-weight-bold text-xs mt-1 mb-0">
                                        Dalam proses review
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Verifikasi -->
<div class="modal fade" id="verifikasiModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('adminui.pendaftaran-sertifikasi.verifikasi', $pendaftaran->id) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-header">
                    <h5 class="modal-title">Verifikasi Pendaftaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Verifikasi pendaftaran <strong>{{ $pendaftaran->nomor_pendaftaran }}</strong>?</p>
                    <div class="form-group">
                        <label>Catatan Admin (Opsional)</label>
                        <textarea name="catatan_admin" class="form-control" rows="3" placeholder="Catatan untuk asesi..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn bg-gradient-success">Verifikasi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Tolak -->
<div class="modal fade" id="tolakModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('adminui.pendaftaran-sertifikasi.tolak', $pendaftaran->id) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-header">
                    <h5 class="modal-title">Tolak Pendaftaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Tolak pendaftaran <strong>{{ $pendaftaran->nomor_pendaftaran }}</strong>?</p>
                    <div class="form-group">
                        <label>Alasan Penolakan <span class="text-danger">*</span></label>
                        <textarea name="catatan_admin" class="form-control" rows="3" placeholder="Jelaskan alasan penolakan..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn bg-gradient-danger">Tolak</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnSimpanSkema = document.getElementById('btnSimpanSkema');
    const formAssignSkema = document.getElementById('formAssignSkema');
    const selectSkema = document.getElementById('skema_sertifikasi_id');
    
    if (btnSimpanSkema && formAssignSkema) {
        btnSimpanSkema.addEventListener('click', function() {
            // Validasi dropdown
            if (!selectSkema.value) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Perhatian!',
                    text: 'Silakan pilih skema sertifikasi terlebih dahulu.',
                    confirmButtonColor: '#5e72e4'
                });
                return;
            }
            
            // Get selected skema text
            const selectedText = selectSkema.options[selectSkema.selectedIndex].text;
            
            Swal.fire({
                title: 'Konfirmasi Penetapan Skema',
                html: `
                    <div class="text-start">
                        <p class="mb-2">Anda akan menetapkan skema:</p>
                        <div class="alert alert-info py-2 px-3 mb-3">
                            <i class="fas fa-certificate me-2"></i>
                            <strong>${selectedText}</strong>
                        </div>
                        <p class="mb-0 text-muted small">
                            <i class="fas fa-info-circle me-1"></i>
                            Status pendaftaran akan berubah menjadi <strong class="text-success">SIAP ASESMEN</strong>
                        </p>
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#5e72e4',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-check me-1"></i> Ya, Tetapkan Skema',
                cancelButtonText: '<i class="fas fa-times me-1"></i> Batal',
                reverseButtons: true,
                customClass: {
                    popup: 'swal-wide'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading
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
                    formAssignSkema.submit();
                }
            });
        });
    }
});
</script>
<style>
.swal-wide {
    max-width: 450px !important;
}
</style>
@endpush
