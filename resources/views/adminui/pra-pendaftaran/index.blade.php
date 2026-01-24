@extends('adminui.layouts.auth')

@section('title', 'Pra-Pendaftaran' . (systemCompanyName() ? ' - ' . systemCompanyName() . ' Admin' : ' - Admin'))

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h6>Daftar Pra-Pendaftaran</h6>
                        <p class="text-sm mb-0">Data pendaftaran dari publik</p>
                    </div>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible mx-4 mt-3" role="alert">
                            <span class="text-white">{{ session('success') }}</span>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Filters -->
                    <div class="px-4 pt-3 pb-2">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <input type="text" name="search" class="form-control" placeholder="Cari nama, email, NIK, NIM..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-2">
                                <select name="status" class="form-select">
                                    <option value="">Semua Status</option>
                                    @foreach($statusLabels as $value => $label)
                                        <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="tipe_peserta" class="form-select">
                                    <option value="">Semua Tipe</option>
                                    @foreach($tipePesertaLabels as $value => $label)
                                        <option value="{{ $value }}" {{ request('tipe_peserta') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn bg-gradient-primary btn-sm">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                                <a href="{{ route('adminui.pra-pendaftaran.index') }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-times"></i> Reset
                                </a>
                            </div>
                        </form>
                    </div>

                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Nama</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Kontak</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tipe</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">NIK/NIM</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tanggal</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pendaftaran as $item)
                                <tr>
                                    <td>
                                        <div class="d-flex px-3 py-1">
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm">{{ $item->nama_lengkap }}</h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">{{ $item->email }}</p>
                                        <p class="text-xs text-secondary mb-0">{{ $item->no_hp }}</p>
                                    </td>
                                    <td class="align-middle text-center">
                                        @if($item->tipe_peserta === 'umum')
                                            <span class="badge badge-sm bg-gradient-info">Umum</span>
                                        @else
                                            <span class="badge badge-sm bg-gradient-warning">Kampus</span>
                                        @endif
                                    </td>
                                    <td class="align-middle text-center">
                                        <span class="text-xs">
                                            @if($item->tipe_peserta === 'umum')
                                                {{ $item->nik ?: '-' }}
                                            @else
                                                {{ $item->nim ?: '-' }}
                                            @endif
                                        </span>
                                    </td>
                                    <td class="align-middle text-center text-sm">
                                        @php
                                            // ========================================================================
                                            // STATUS BADGE COLORS - ONLY 3 VALID STATES
                                            // ========================================================================
                                            $statusColors = [
                                                'menunggu_verifikasi' => 'bg-gradient-secondary',
                                                'diterima' => 'bg-gradient-success',
                                                'ditolak' => 'bg-gradient-danger',
                                            ];
                                        @endphp
                                        <span class="badge badge-sm {{ $statusColors[$item->status] ?? 'bg-gradient-secondary' }}">
                                            {{ $statusLabels[$item->status] ?? $item->status }}
                                        </span>
                                    </td>
                                    <td class="align-middle text-center">
                                        <span class="text-xs text-secondary">{{ $item->created_at->format('d/m/Y H:i') }}</span>
                                    </td>
                                    <td class="align-middle text-center">
                                        {{-- ========================================================================
                                             AKSI: HANYA LIHAT DETAIL
                                             ========================================================================
                                             Prinsip: Pra-Pendaftaran TIDAK boleh ada logic skema
                                             Admin hanya bisa:
                                             1. Lihat detail (untuk verifikasi)
                                             2. Terima/Tolak (di halaman detail)
                                             
                                             TIDAK BOLEH:
                                             ❌ Pilih skema di sini
                                             ❌ Buat pendaftaran sertifikasi dari sini
                                             ❌ Modal penetapan skema
                                             
                                             Penetapan skema dilakukan di modul Pendaftaran Sertifikasi
                                         ======================================================================== --}}
                                        <a href="{{ route('adminui.pra-pendaftaran.show', $item->id) }}" 
                                           class="btn btn-link text-info px-2 mb-0" 
                                           title="Lihat Detail & Verifikasi">
                                            <i class="fas fa-eye"></i> Detail
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <p class="text-sm mb-0">Belum ada data pra-pendaftaran.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($pendaftaran->hasPages())
                    <div class="px-4 pt-3">
                        {{ $pendaftaran->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ========================================================================
     NO MODAL FOR SKEMA ASSIGNMENT IN PRA-PENDAFTARAN MODULE
     ========================================================================
     Prinsip Clean Architecture:
     - Pra-Pendaftaran = Data verification only
     - Pendaftaran Sertifikasi = Skema assignment (separate module)
     
     Admin workflow:
     1. Verify documents in Pra-Pendaftaran (Terima/Tolak)
     2. Go to Pendaftaran Sertifikasi module
     3. Create Pendaftaran from approved Pra-Pendaftaran with Skema
     
     Modal removed to prevent confusion and enforce clean separation
     ======================================================================== --}}

@endsection
