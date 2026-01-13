@extends('adminui.layouts.auth')

@section('title', 'Detail Audit Log' . (systemCompanyName() ? ' - ' . systemCompanyName() . ' Admin' : ' - Admin'))

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6><i class="fas fa-file-alt me-2 text-primary"></i>Detail Audit Log</h6>
                            <p class="text-sm mb-0">ID: #{{ $log->id }}</p>
                        </div>
                        <a href="{{ route('adminui.audit-log.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Main Info -->
                        <div class="col-lg-6">
                            <div class="card bg-gradient-light mb-4">
                                <div class="card-header pb-0">
                                    <h6 class="text-sm"><i class="fas fa-info-circle me-2"></i>Informasi Utama</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <td class="text-secondary" width="150">Waktu</td>
                                            <td>
                                                <strong>{{ $log->created_at->format('d F Y, H:i:s') }}</strong>
                                                <span class="text-muted">({{ $log->created_at->diffForHumans() }})</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-secondary">User</td>
                                            <td>
                                                <strong>{{ $log->user_name }}</strong>
                                                @if($log->user)
                                                <a href="#" class="text-info ms-1" title="Lihat User">
                                                    <i class="fas fa-external-link-alt text-xs"></i>
                                                </a>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-secondary">Role</td>
                                            <td>
                                                <span class="badge bg-gradient-info">{{ $log->user_role }}</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-secondary">Aksi</td>
                                            <td>
                                                <span class="badge {{ $log->action_badge }}">{{ $log->action_label }}</span>
                                                <span class="text-muted ms-1">({{ $log->action }})</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-secondary">Modul</td>
                                            <td>
                                                <span class="badge bg-gradient-secondary">{{ $log->module_label }}</span>
                                                <span class="text-muted ms-1">({{ $log->module }})</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-secondary">Reference</td>
                                            <td>
                                                @if($log->reference_number)
                                                <strong class="text-primary">{{ $log->reference_number }}</strong>
                                                @else
                                                <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="card mb-4">
                                <div class="card-header pb-0">
                                    <h6 class="text-sm"><i class="fas fa-align-left me-2"></i>Deskripsi</h6>
                                </div>
                                <div class="card-body">
                                    <p class="mb-0">{{ $log->description }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Technical Info -->
                        <div class="col-lg-6">
                            <div class="card bg-gradient-light mb-4">
                                <div class="card-header pb-0">
                                    <h6 class="text-sm"><i class="fas fa-server me-2"></i>Informasi Teknis</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <td class="text-secondary" width="120">Model</td>
                                            <td>
                                                @if($log->model_type)
                                                <code class="text-xs">{{ $log->model_type }}</code>
                                                @else
                                                <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-secondary">Model ID</td>
                                            <td>
                                                @if($log->model_id)
                                                <strong>#{{ $log->model_id }}</strong>
                                                @else
                                                <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-secondary">IP Address</td>
                                            <td>
                                                <code>{{ $log->ip_address ?? '-' }}</code>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-secondary">Method</td>
                                            <td>
                                                <span class="badge bg-gradient-secondary">{{ $log->method ?? '-' }}</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-secondary">URL</td>
                                            <td>
                                                <code class="text-xs" style="word-break: break-all;">
                                                    {{ $log->url ?? '-' }}
                                                </code>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <!-- User Agent -->
                            @if($log->user_agent)
                            <div class="card mb-4">
                                <div class="card-header pb-0">
                                    <h6 class="text-sm"><i class="fas fa-globe me-2"></i>User Agent</h6>
                                </div>
                                <div class="card-body">
                                    <code class="text-xs" style="word-break: break-all;">
                                        {{ $log->user_agent }}
                                    </code>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Data Changes -->
                    <div class="row">
                        @if($log->old_values)
                        <div class="col-lg-6">
                            <div class="card border border-danger mb-4">
                                <div class="card-header pb-0 bg-gradient-danger text-white">
                                    <h6 class="text-sm text-white"><i class="fas fa-minus-circle me-2"></i>Data Sebelum (Old Values)</h6>
                                </div>
                                <div class="card-body">
                                    <pre class="bg-light p-3 rounded text-xs" style="max-height: 300px; overflow: auto;">{{ json_encode($log->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($log->new_values)
                        <div class="col-lg-6">
                            <div class="card border border-success mb-4">
                                <div class="card-header pb-0 bg-gradient-success text-white">
                                    <h6 class="text-sm text-white"><i class="fas fa-plus-circle me-2"></i>Data Sesudah (New Values)</h6>
                                </div>
                                <div class="card-body">
                                    <pre class="bg-light p-3 rounded text-xs" style="max-height: 300px; overflow: auto;">{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($log->metadata)
                        <div class="col-12">
                            <div class="card border border-info mb-4">
                                <div class="card-header pb-0 bg-gradient-info text-white">
                                    <h6 class="text-sm text-white"><i class="fas fa-database me-2"></i>Metadata</h6>
                                </div>
                                <div class="card-body">
                                    <pre class="bg-light p-3 rounded text-xs" style="max-height: 300px; overflow: auto;">{{ json_encode($log->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
