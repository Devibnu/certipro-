@extends('adminui.layouts.auth')

@section('title', 'Detail Pra-Pendaftaran' . (systemCompanyName() ? ' - ' . systemCompanyName() . ' Admin' : ' - Admin'))

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6>Detail Pra-Pendaftaran</h6>
                            <p class="text-sm mb-0">
                                <span class="badge bg-gradient-dark me-2">{{ $data->nomor_pra_pendaftaran ?? 'N/A' }}</span>
                                ID: #{{ $data->id }}
                            </p>
                        </div>
                        <a href="{{ route('adminui.pra-pendaftaran.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible" role="alert">
                            <span class="text-white">{{ session('success') }}</span>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible" role="alert">
                            <span class="text-white">{{ session('error') }}</span>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="row">
                        <!-- Data Pribadi -->
                        <div class="col-12 mb-4">
                            <h6 class="text-uppercase text-sm text-primary mb-3">
                                <i class="fas fa-user-circle me-2"></i>Data Pribadi
                            </h6>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <tr>
                                        <th width="200" class="bg-light">Nama Lengkap</th>
                                        <td>{{ $data->nama_lengkap }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">Email</th>
                                        <td>
                                            <a href="mailto:{{ $data->email }}">{{ $data->email }}</a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">No. HP/WhatsApp</th>
                                        <td>
                                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $data->no_hp) }}" target="_blank">
                                                {{ $data->no_hp }} <i class="fab fa-whatsapp text-success"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">Tipe Peserta</th>
                                        <td>
                                            @if($data->tipe_peserta === 'umum')
                                                <span class="badge bg-info">Umum</span>
                                            @else
                                                <span class="badge bg-warning">Kampus</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @if($data->tipe_peserta === 'umum')
                                    <tr>
                                        <th class="bg-light">NIK</th>
                                        <td>{{ $data->nik ?: '-' }}</td>
                                    </tr>
                                    @else
                                    <tr>
                                        <th class="bg-light">NIM</th>
                                        <td>{{ $data->nim ?: '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">Institusi</th>
                                        <td>{{ $data->institusi ?: '-' }}</td>
                                    </tr>
                                    @endif
                                </table>
                            </div>
                        </div>

                        <!-- File Identitas -->
                        @if($data->upload_identitas)
                        <div class="col-12 mb-4">
                            <h6 class="text-uppercase text-sm text-primary mb-3">
                                <i class="fas fa-id-card me-2"></i>Dokumen Identitas
                            </h6>
                            <div class="p-3 bg-light rounded">
                                @php
                                    $extension = pathinfo($data->upload_identitas, PATHINFO_EXTENSION);
                                @endphp
                                @if(in_array(strtolower($extension), ['jpg', 'jpeg', 'png']))
                                    <img src="{{ asset('storage/' . $data->upload_identitas) }}" alt="Identitas" class="img-fluid rounded" style="max-height: 300px;">
                                @else
                                    <a href="{{ asset('storage/' . $data->upload_identitas) }}" target="_blank" class="btn btn-outline-primary">
                                        <i class="fas fa-file-pdf me-2"></i> Lihat Dokumen PDF
                                    </a>
                                @endif
                            </div>
                        </div>
                        @endif

                        <!-- Info Waktu -->
                        <div class="col-12">
                            <h6 class="text-uppercase text-sm text-primary mb-3">
                                <i class="fas fa-clock me-2"></i>Informasi Waktu
                            </h6>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <tr>
                                        <th width="200" class="bg-light">Tanggal Daftar</th>
                                        <td>{{ $data->created_at->format('d F Y, H:i') }} WIB</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">Terakhir Update</th>
                                        <td>{{ $data->updated_at->format('d F Y, H:i') }} WIB</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar - Update Status -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6>Update Status</h6>
                </div>
                <div class="card-body">
                    @php
                        $statusColors = [
                            'baru' => 'secondary',
                            'diproses' => 'info',
                            'diterima' => 'success',
                            'ditolak' => 'danger',
                        ];
                    @endphp
                    
                    <div class="mb-3">
                        <span class="text-sm">Status Saat Ini:</span>
                        <h5>
                            <span class="badge bg-{{ $statusColors[$data->status] ?? 'secondary' }}">
                                {{ $statusLabels[$data->status] ?? $data->status }}
                            </span>
                        </h5>
                    </div>

                    <form action="{{ route('adminui.pra-pendaftaran.update-status', $data->id) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <div class="mb-3">
                            <label class="form-label">Ubah Status</label>
                            <select name="status" class="form-select" id="statusSelect" onchange="toggleReasonField()">
                                @foreach($statusLabels as $value => $label)
                                    <option value="{{ $value }}" {{ $data->status === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Alasan Penolakan (muncul jika status = ditolak) -->
                        <div class="mb-3" id="rejectionReasonField" style="{{ $data->status === 'ditolak' ? '' : 'display: none;' }}">
                            <label class="form-label text-danger">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                Alasan Penolakan <span class="text-xs">(wajib diisi)</span>
                            </label>
                            <textarea name="alasan_penolakan" class="form-control" rows="4" 
                                      placeholder="Tuliskan alasan penolakan secara jelas dan formal. Alasan ini akan dikirim ke peserta.">{{ $data->alasan_penolakan }}</textarea>
                            @error('alasan_penolakan')
                                <div class="text-danger text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Tampilkan alasan penolakan jika sudah ada -->
                        @if($data->status === 'ditolak' && $data->alasan_penolakan)
                        <div class="alert alert-danger mb-3" role="alert">
                            <h6 class="alert-heading text-white text-sm mb-2">
                                <i class="fas fa-ban me-1"></i> Alasan Penolakan Tercatat:
                            </h6>
                            <p class="text-white text-sm mb-0">{{ $data->alasan_penolakan }}</p>
                            @if($data->status_updated_at)
                                <hr class="text-white">
                                <p class="text-white text-xs mb-0">
                                    <i class="fas fa-clock me-1"></i>
                                    Diperbarui: {{ $data->status_updated_at->format('d M Y, H:i') }} WIB
                                    @if($data->statusUpdatedBy)
                                        oleh {{ $data->statusUpdatedBy->name }}
                                    @endif
                                </p>
                            @endif
                        </div>
                        @endif
                        
                        <button type="submit" class="btn bg-gradient-primary w-100">
                            <i class="fas fa-save me-2"></i> Simpan Status
                        </button>
                    </form>
                    
                    <script>
                        function toggleReasonField() {
                            const status = document.getElementById('statusSelect').value;
                            const reasonField = document.getElementById('rejectionReasonField');
                            if (status === 'ditolak') {
                                reasonField.style.display = 'block';
                            } else {
                                reasonField.style.display = 'none';
                            }
                        }
                    </script>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header pb-0">
                    <h6>Aksi Cepat</h6>
                </div>
                <div class="card-body">
                    <a href="mailto:{{ $data->email }}" class="btn btn-outline-info w-100 mb-2">
                        <i class="fas fa-envelope me-2"></i> Kirim Email
                    </a>
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $data->no_hp) }}" target="_blank" class="btn btn-outline-success w-100 mb-2">
                        <i class="fab fa-whatsapp me-2"></i> Chat WhatsApp
                    </a>
                    
                    @if($data->status === 'diterima')
                        @if($data->hasPendaftaranSertifikasi())
                            <div class="alert alert-info mt-3 mb-0" role="alert">
                                <i class="fas fa-check-circle me-2"></i>
                                <span class="text-white">Pendaftaran sertifikasi sudah dibuat.</span>
                                <br>
                                <a href="{{ route('adminui.pendaftaran-sertifikasi.show', $data->pendaftaranSertifikasi->id) }}" class="btn btn-sm btn-white mt-2">
                                    <i class="fas fa-eye me-1"></i> Lihat Pendaftaran
                                </a>
                            </div>
                        @else
                            {{-- FEATURE REMOVED: Automatic pendaftaran creation from pra-pendaftaran --}}
                            <div class="alert alert-warning mt-3 mb-0" role="alert">
                                <i class="fas fa-info-circle me-2"></i>
                                <span>Pendaftaran sertifikasi belum dibuat. Silakan buat pendaftaran manual dari menu Pendaftaran Sertifikasi.</span>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
