@extends('adminui.layouts.auth')

@section('title', 'Pendaftaran Sertifikasi' . (systemCompanyName() ? ' - ' . systemCompanyName() . ' Admin' : ' - Admin'))

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h6>Pendaftaran Sertifikasi</h6>
                        <p class="text-sm mb-0">Kelola pendaftaran asesi ke skema sertifikasi</p>
                    </div>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible mx-4 mt-3" role="alert">
                            <span class="text-white">{{ session('success') }}</span>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible mx-4 mt-3" role="alert">
                            <span class="text-white">{{ session('error') }}</span>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Filters -->
                    <div class="px-4 pt-3 pb-2">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <select name="status" class="form-select">
                                    <option value="">Semua Status</option>
                                    @foreach($statusLabels as $value => $label)
                                        <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="skema_sertifikasi_id" class="form-select">
                                    <option value="">Semua Skema</option>
                                    @foreach($skemaList as $skema)
                                        <option value="{{ $skema->id }}" {{ request('skema_sertifikasi_id') == $skema->id ? 'selected' : '' }}>
                                            {{ $skema->kode_skema }} - {{ $skema->nama_skema }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input type="text" name="search" class="form-control" placeholder="Cari nomor/nama asesi..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn bg-gradient-primary btn-sm">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                                <a href="{{ route('adminui.pendaftaran-sertifikasi.index') }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-times"></i> Reset
                                </a>
                            </div>
                        </form>
                    </div>

                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">No. Pendaftaran</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Asesi</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Skema</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tanggal</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pendaftaran as $item)
                                <tr>
                                    <td>
                                        <div class="d-flex px-3 py-1">
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm font-weight-bold">{{ $item->nomor_pendaftaran }}</h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <p class="text-sm font-weight-bold mb-0">{{ $item->user->name ?? $item->nama_lengkap ?? '-' }}</p>
                                        <p class="text-xs text-secondary mb-0">{{ $item->user->email ?? $item->email ?? '-' }}</p>
                                    </td>
                                    <td>
                                        <span class="badge badge-sm bg-gradient-info">
                                            {{ $item->skemaSertifikasi->kode_skema ?? '-' }}
                                        </span>
                                        <p class="text-xs text-secondary mb-0">{{ $item->skemaSertifikasi->nama_skema ?? '-' }}</p>
                                    </td>
                                    <td class="align-middle text-center">
                                        <span class="text-sm">{{ $item->tanggal_daftar->format('d M Y') }}</span>
                                    </td>
                                    <td class="align-middle text-center text-sm">
                                        <span class="badge badge-sm {{ $item->status_badge }}">
                                            {{ $item->status_label }}
                                        </span>
                                    </td>
                                    <td class="align-middle text-center">
                                        <a href="{{ route('adminui.pendaftaran-sertifikasi.show', $item->id) }}" 
                                           class="btn btn-link text-info px-2 mb-0" title="Detail">
                                            <i class="fas fa-eye text-info"></i>
                                        </a>
                                        @if($item->isDiajukan())
                                            <button type="button" 
                                                    class="btn btn-link text-success px-2 mb-0" 
                                                    title="Verifikasi"
                                                    onclick="verifikasiModal({{ $item->id }}, '{{ $item->nomor_pendaftaran }}')">
                                                <i class="fas fa-check text-success"></i>
                                            </button>
                                            <button type="button" 
                                                    class="btn btn-link text-danger px-2 mb-0" 
                                                    title="Tolak"
                                                    onclick="tolakModal({{ $item->id }}, '{{ $item->nomor_pendaftaran }}')">
                                                <i class="fas fa-times text-danger"></i>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <p class="text-sm mb-0">Belum ada pendaftaran sertifikasi.</p>
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

<!-- Modal Verifikasi -->
<div class="modal fade" id="verifikasiModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="verifikasiForm" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-header">
                    <h5 class="modal-title">Verifikasi Pendaftaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Verifikasi pendaftaran <strong id="verifikasiNomor"></strong>?</p>
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
            <form id="tolakForm" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-header">
                    <h5 class="modal-title">Tolak Pendaftaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Tolak pendaftaran <strong id="tolakNomor"></strong>?</p>
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
<script>
function verifikasiModal(id, nomor) {
    document.getElementById('verifikasiNomor').textContent = nomor;
    document.getElementById('verifikasiForm').action = '{{ url("adminui/pendaftaran-sertifikasi") }}/' + id + '/verifikasi';
    new bootstrap.Modal(document.getElementById('verifikasiModal')).show();
}

function tolakModal(id, nomor) {
    document.getElementById('tolakNomor').textContent = nomor;
    document.getElementById('tolakForm').action = '{{ url("adminui/pendaftaran-sertifikasi") }}/' + id + '/tolak';
    new bootstrap.Modal(document.getElementById('tolakModal')).show();
}
</script>
@endpush
