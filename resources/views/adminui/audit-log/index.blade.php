@extends('adminui.layouts.auth')

@section('title', 'Audit Log Sistem' . (systemCompanyName() ? ' - ' . systemCompanyName() . ' Admin' : ' - Admin'))

@push('styles')
<style>
    .audit-header {
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
        border-radius: 1rem;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }
    .audit-header h4 {
        color: #fff;
        font-weight: 700;
        margin-bottom: 0.25rem;
    }
    .audit-header p {
        color: rgba(255,255,255,0.7);
        margin-bottom: 0;
    }
    .stat-card {
        background: #fff;
        border-radius: 0.75rem;
        padding: 1.25rem;
        border: 1px solid #e9ecef;
        transition: all 0.2s ease;
    }
    .stat-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        transform: translateY(-2px);
    }
    .stat-card .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 0.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }
    .stat-card .stat-value {
        font-size: 1.75rem;
        font-weight: 700;
        color: #344767;
        line-height: 1;
    }
    .stat-card .stat-label {
        font-size: 0.75rem;
        color: #67748e;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-top: 0.25rem;
    }
    .filter-card {
        border: 1px solid #e9ecef;
        border-radius: 0.75rem;
    }
    .filter-card .card-header {
        background: #f8f9fa;
        border-bottom: 1px solid #e9ecef;
        padding: 0.875rem 1.25rem;
    }
    .filter-card .card-header h6 {
        font-size: 0.875rem;
        font-weight: 600;
        color: #344767;
        margin: 0;
    }
    .audit-table thead th {
        background: #f8f9fa;
        border-bottom: 2px solid #e9ecef;
        position: sticky;
        top: 0;
        z-index: 10;
    }
    .audit-table tbody tr {
        border-bottom: 1px solid #f0f2f5;
    }
    .audit-table tbody tr:nth-child(even) {
        background-color: #fafbfc;
    }
    .audit-table tbody tr:hover {
        background-color: #f0f7ff !important;
    }
    .time-badge {
        background: #e9ecef;
        border-radius: 0.5rem;
        padding: 0.5rem 0.75rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
    .time-badge i {
        color: #67748e;
    }
    .user-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-weight: 600;
        font-size: 0.75rem;
    }
    .event-badge-create { background: linear-gradient(135deg, #11998e, #38ef7d); color: #fff; }
    .event-badge-update { background: linear-gradient(135deg, #667eea, #764ba2); color: #fff; }
    .event-badge-delete { background: linear-gradient(135deg, #eb3349, #f45c43); color: #fff; }
    .event-badge-login { background: linear-gradient(135deg, #6a11cb, #2575fc); color: #fff; }
    .event-badge-logout { background: linear-gradient(135deg, #6c757d, #495057); color: #fff; }
    .event-badge-approve { background: linear-gradient(135deg, #00b09b, #96c93d); color: #fff; }
    .event-badge-reject { background: linear-gradient(135deg, #ff416c, #ff4b2b); color: #fff; }
    .event-badge-verify { background: linear-gradient(135deg, #1e3c72, #2a5298); color: #fff; }
    .event-badge-issue { background: linear-gradient(135deg, #00c6ff, #0072ff); color: #fff; }
    .event-badge-revoke { background: linear-gradient(135deg, #f12711, #f5af19); color: #fff; }
    .event-badge-decide { background: linear-gradient(135deg, #fc4a1a, #f7b733); color: #fff; }
    .empty-state {
        padding: 4rem 2rem;
        text-align: center;
    }
    .empty-state .icon-wrapper {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: linear-gradient(135deg, #e9ecef, #f8f9fa);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
    }
    .empty-state .icon-wrapper i {
        font-size: 2rem;
        color: #adb5bd;
    }
    .reference-code {
        font-family: 'Monaco', 'Consolas', monospace;
        font-size: 0.75rem;
        background: #e8f4fd;
        color: #0d6efd;
        padding: 0.25rem 0.5rem;
        border-radius: 0.375rem;
        border: 1px solid #b6d4fe;
    }
    .bnsp-badge {
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
        color: #fff;
        font-size: 0.625rem;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
        letter-spacing: 1px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <!-- Audit Header -->
    <div class="audit-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="d-flex align-items-center gap-3">
                    <div class="icon-wrapper" style="width: 56px; height: 56px; background: rgba(255,255,255,0.1); border-radius: 1rem; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-shield-alt text-white" style="font-size: 1.5rem;"></i>
                    </div>
                    <div>
                        <h4><i class="fas fa-history me-2"></i>Audit Log Sistem</h4>
                        <p class="mb-0">Jejak aktivitas untuk audit & kepatuhan BNSP <span class="bnsp-badge ms-2">READ-ONLY</span></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                @if(auth()->user()->role === 'super admin')
                <a href="{{ route('adminui.audit-evidence.pdf') }}" 
                   class="btn btn-warning btn-sm me-2" target="_blank">
                    <i class="fas fa-file-pdf me-1"></i> Cetak Audit Evidence
                </a>
                @endif
                <a href="{{ route('adminui.audit-log.export', request()->all()) }}" 
                   class="btn btn-light btn-sm">
                    <i class="fas fa-file-csv me-1"></i> Export CSV
                </a>
            </div>
        </div>
    </div>

    <!-- Summary Statistics -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-gradient-primary text-white me-3">
                        <i class="fas fa-database"></i>
                    </div>
                    <div>
                        <div class="stat-value">{{ number_format($stats['total']) }}</div>
                        <div class="stat-label">Total Log</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-gradient-success text-white me-3">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div>
                        <div class="stat-value">{{ number_format($stats['today']) }}</div>
                        <div class="stat-label">Hari Ini</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-gradient-info text-white me-3">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>
                        <div class="stat-value">{{ number_format($stats['active_users']) }}</div>
                        <div class="stat-label">User Aktif</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-gradient-warning text-white me-3">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div>
                        <div class="stat-value">{{ number_format($stats['critical_events']) }}</div>
                        <div class="stat-label">Event Kritis (7 Hari)</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="card filter-card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6><i class="fas fa-filter me-2 text-primary"></i>Filter Audit</h6>
            <span class="text-xs text-secondary">
                <i class="fas fa-info-circle me-1"></i>Gunakan filter untuk mempersempit hasil pencarian
            </span>
        </div>
        <div class="card-body">
            <form method="GET">
                <div class="row g-3 mb-3">
                    <div class="col-xl-3 col-md-6">
                        <label class="form-label text-xs text-uppercase text-secondary">Modul</label>
                        <select name="module" class="form-select">
                            <option value="">Semua Modul</option>
                            @foreach($modules as $key => $label)
                            <option value="{{ $key }}" {{ request('module') == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <label class="form-label text-xs text-uppercase text-secondary">Event</label>
                        <select name="event" class="form-select">
                            <option value="">Semua Event</option>
                            @foreach($events as $key => $label)
                            <option value="{{ $key }}" {{ request('event') == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <label class="form-label text-xs text-uppercase text-secondary">User</label>
                        <select name="user_id" class="form-select">
                            <option value="">Semua User</option>
                            @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} ({{ ucfirst($user->role) }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <label class="form-label text-xs text-uppercase text-secondary">Pencarian</label>
                        <input type="text" name="search" class="form-control" 
                               placeholder="Cari deskripsi, reference..."
                               value="{{ request('search') }}">
                    </div>
                </div>
                <div class="row g-3 align-items-end">
                    <div class="col-xl-3 col-md-6">
                        <label class="form-label text-xs text-uppercase text-secondary">Dari Tanggal</label>
                        <input type="date" name="start_date" class="form-control" 
                               value="{{ request('start_date') }}">
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <label class="form-label text-xs text-uppercase text-secondary">Sampai Tanggal</label>
                        <input type="date" name="end_date" class="form-control" 
                               value="{{ request('end_date') }}">
                    </div>
                    <div class="col-xl-6 col-md-12">
                        <div class="d-flex gap-2 justify-content-md-end">
                            <button type="submit" class="btn bg-gradient-primary">
                                <i class="fas fa-search me-1"></i> Terapkan Filter
                            </button>
                            <a href="{{ route('adminui.audit-log.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-undo me-1"></i> Reset
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Audit Log Table -->
    <div class="card">
        <div class="card-header pb-0 d-flex justify-content-between align-items-center">
            <div>
                <h6 class="mb-0">Riwayat Aktivitas</h6>
                <p class="text-sm text-secondary mb-0">
                    Menampilkan <strong>{{ $logs->count() }}</strong> dari <strong>{{ number_format($logs->total()) }}</strong> log
                    @if(request()->hasAny(['module', 'event', 'user_id', 'start_date', 'end_date', 'search']))
                    <span class="badge bg-gradient-info ms-2">Filter Aktif</span>
                    @endif
                </p>
            </div>
            <div>
                <span class="text-xs text-secondary">
                    <i class="fas fa-clock me-1"></i>Waktu: WIB (UTC+7)
                </span>
            </div>
        </div>
        <div class="card-body px-0 pt-0 pb-2">
            <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                <table class="table audit-table align-items-center mb-0">
                    <thead>
                        <tr>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder ps-4" style="min-width: 160px;">
                                <i class="fas fa-clock me-1 opacity-6"></i>Waktu
                            </th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder ps-2" style="min-width: 180px;">
                                <i class="fas fa-user me-1 opacity-6"></i>User
                            </th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder ps-2" style="min-width: 100px;">
                                Role
                            </th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder ps-2" style="min-width: 120px;">
                                <i class="fas fa-cube me-1 opacity-6"></i>Modul
                            </th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder ps-2" style="min-width: 130px;">
                                <i class="fas fa-bolt me-1 opacity-6"></i>Event
                            </th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder ps-2" style="min-width: 250px;">
                                Deskripsi
                            </th>
                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder" style="min-width: 140px;">
                                Reference ID
                            </th>
                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder" style="min-width: 80px;">
                                Detail
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        <tr>
                            <td class="ps-4">
                                <div class="time-badge">
                                    <i class="fas fa-clock"></i>
                                    <div>
                                        <div class="text-xs font-weight-bold">{{ $log->created_at->format('d M Y') }}</div>
                                        <div class="text-xxs text-secondary">{{ $log->created_at->format('H:i:s') }} WIB</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="user-avatar me-2">
                                        {{ strtoupper(substr($log->user_name ?? 'S', 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-weight-bold mb-0">{{ $log->user_name ?? 'System' }}</p>
                                        <p class="text-xxs text-secondary mb-0">{{ $log->user?->email ?? '-' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-sm bg-gradient-secondary">
                                    {{ ucfirst($log->user_role ?? 'system') }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-sm bg-gradient-dark">
                                    {{ $log->module_label }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $eventClass = match($log->action) {
                                        'create' => 'event-badge-create',
                                        'update' => 'event-badge-update',
                                        'delete' => 'event-badge-delete',
                                        'login' => 'event-badge-login',
                                        'logout' => 'event-badge-logout',
                                        'approve' => 'event-badge-approve',
                                        'reject' => 'event-badge-reject',
                                        'verify' => 'event-badge-verify',
                                        'issue' => 'event-badge-issue',
                                        'revoke' => 'event-badge-revoke',
                                        'decide' => 'event-badge-decide',
                                        default => 'bg-gradient-secondary'
                                    };
                                @endphp
                                <span class="badge badge-sm {{ $eventClass }}">
                                    {{ $log->event_label ?? $log->action_label }}
                                </span>
                            </td>
                            <td>
                                <p class="text-sm mb-0" title="{{ $log->description }}">
                                    {{ Str::limit($log->description, 50) }}
                                </p>
                            </td>
                            <td class="align-middle text-center">
                                @if($log->reference_number)
                                <span class="reference-code">{{ $log->reference_number }}</span>
                                @else
                                <span class="text-xs text-secondary">-</span>
                                @endif
                            </td>
                            <td class="align-middle text-center">
                                <a href="{{ route('adminui.audit-log.show', $log->id) }}" 
                                   class="btn btn-link text-info mb-0 px-2" 
                                   data-bs-toggle="tooltip" 
                                   title="Lihat Detail Lengkap">
                                    <i class="fas fa-search-plus"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <div class="icon-wrapper">
                                        <i class="fas fa-shield-alt"></i>
                                    </div>
                                    <h6 class="text-secondary mb-2">Belum ada aktivitas tercatat</h6>
                                    <p class="text-sm text-secondary mb-0">
                                        Audit log akan otomatis terisi saat sistem digunakan.<br>
                                        Semua aktivitas akan dicatat secara otomatis untuk keperluan audit BNSP.
                                    </p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($logs->hasPages())
            <div class="px-4 pt-3 pb-2 border-top">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-sm text-secondary">
                        Halaman {{ $logs->currentPage() }} dari {{ $logs->lastPage() }}
                    </div>
                    <div>
                        {{ $logs->withQueryString()->links() }}
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Footer Info -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="alert alert-light border text-sm mb-0" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-info-circle text-primary me-2"></i>
                    <div>
                        <strong>Informasi Audit:</strong> 
                        Log ini bersifat <strong>read-only</strong> dan tidak dapat diubah untuk memenuhi standar kepatuhan BNSP.
                        Data disimpan secara permanen untuk keperluan audit dan pelacakan aktivitas sistem.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
</script>
@endpush
