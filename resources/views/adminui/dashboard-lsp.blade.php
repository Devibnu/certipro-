{{--
================================================================================
Dashboard Admin LSP - CertiPro
================================================================================
File: resources/views/adminui/dashboard-lsp.blade.php
Purpose: Dashboard operasional LSP dengan KPI sertifikasi
Compliance: ISO 17024:2012, BNSP Pedoman 201

Catatan untuk Auditor:
Dashboard ini menampilkan kondisi operasional LSP secara real-time:
- Jumlah proses sertifikasi di setiap tahap
- Alur sertifikasi visual dengan kontrol poin
- Alert untuk proses yang memerlukan perhatian
- Aktivitas sistem terakhir sebagai bukti tata kelola

Semua data diambil langsung dari database (bukan data dummy).
================================================================================
--}}

@extends('adminui.layouts.auth')

@section('title', 'Dashboard - CertiPro LSP')

@section('content')
<div class="container-fluid py-4">
    
    {{-- ================================================================== --}}
    {{-- HEADER SECTION --}}
    {{-- ================================================================== --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1 font-weight-bolder">Dashboard Operasional LSP</h4>
                    <p class="text-sm text-secondary mb-0" id="last-updated-container">
                        <i class="fas fa-sync-alt me-1" id="sync-icon"></i>
                        <span id="last-updated-text">Data terakhir diperbarui: {{ now()->format('d M Y, H:i:s') }} WIB</span>
                        <span id="realtime-status" class="badge bg-gradient-success ms-2" style="font-size: 0.65rem;">
                            <i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i>LIVE
                        </span>
                    </p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-outline-primary mb-0" id="btn-refresh-stats" title="Refresh Data">
                        <i class="fas fa-sync-alt me-1"></i> Refresh
                    </button>
                    <span class="badge bg-gradient-success">
                        <i class="fas fa-shield-alt me-1"></i> ISO 17024 Compliant
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- ================================================================== --}}
    {{-- A. KPI UTAMA - KARTU STATISTIK --}}
    {{-- ================================================================== --}}
    
    {{-- Baris 1: Pra-Pendaftaran & Pendaftaran --}}
    <div class="row">
        {{-- Card: Pra-Pendaftaran --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-uppercase font-weight-bold text-secondary">Pra-Pendaftaran</p>
                                <h4 class="font-weight-bolder mb-0" data-stat="pra_pendaftaran.total">{{ number_format($praPendaftaranStats['total']) }}</h4>
                                @if($praPendaftaranStats['new_7_days'] > 0)
                                <p class="mb-0 text-sm">
                                    <span class="text-success font-weight-bolder" data-stat="pra_pendaftaran.new_7_days_formatted">+{{ $praPendaftaranStats['new_7_days'] }}</span>
                                    <span class="text-secondary">7 hari terakhir</span>
                                </p>
                                @else
                                <p class="mb-0 text-sm" id="pra-pendaftaran-trend" style="display:none;">
                                    <span class="text-success font-weight-bolder" data-stat="pra_pendaftaran.new_7_days_formatted"></span>
                                    <span class="text-secondary">7 hari terakhir</span>
                                </p>
                                @endif
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md">
                                <i class="fas fa-user-plus text-lg opacity-10"></i>
                            </div>
                        </div>
                    </div>
                    <hr class="horizontal dark my-3">
                    <div class="d-flex justify-content-between text-xs">
                        <span>
                            <i class="fas fa-clock text-warning me-1"></i>
                            Menunggu: <strong data-stat="pra_pendaftaran.menunggu_verifikasi">{{ $praPendaftaranStats['menunggu_verifikasi'] }}</strong>
                        </span>
                        <span>
                            <i class="fas fa-check text-success me-1"></i>
                            Diterima: <strong data-stat="pra_pendaftaran.diterima">{{ $praPendaftaranStats['diterima'] }}</strong>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card: Pendaftaran Sertifikasi --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-uppercase font-weight-bold text-secondary">Pendaftaran</p>
                                <h4 class="font-weight-bolder mb-0" data-stat="pendaftaran.total">{{ number_format($pendaftaranStats['total']) }}</h4>
                                @if($pendaftaranStats['new_7_days'] > 0)
                                <p class="mb-0 text-sm">
                                    <span class="text-success font-weight-bolder" data-stat="pendaftaran.new_7_days_formatted">+{{ $pendaftaranStats['new_7_days'] }}</span>
                                    <span class="text-secondary">7 hari terakhir</span>
                                </p>
                                @else
                                <p class="mb-0 text-sm" id="pendaftaran-trend" style="display:none;">
                                    <span class="text-success font-weight-bolder" data-stat="pendaftaran.new_7_days_formatted"></span>
                                    <span class="text-secondary">7 hari terakhir</span>
                                </p>
                                @endif
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-info shadow text-center border-radius-md">
                                <i class="fas fa-file-signature text-lg opacity-10"></i>
                            </div>
                        </div>
                    </div>
                    <hr class="horizontal dark my-3">
                    <div class="d-flex justify-content-between text-xs">
                        <span>
                            <i class="fas fa-clipboard-check text-info me-1"></i>
                            Siap Asesmen: <strong data-stat="pendaftaran.siap_asesmen">{{ $pendaftaranStats['siap_asesmen'] }}</strong>
                        </span>
                        <span>
                            <i class="fas fa-hourglass-half text-warning me-1"></i>
                            Menunggu: <strong data-stat="pendaftaran.menunggu_keputusan">{{ $pendaftaranStats['menunggu_keputusan'] }}</strong>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card: Asesmen --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-uppercase font-weight-bold text-secondary">Asesmen</p>
                                <h4 class="font-weight-bolder mb-0" data-stat="asesmen.total">{{ number_format($asesmenStats['total']) }}</h4>
                                @if($asesmenStats['new_7_days'] > 0)
                                <p class="mb-0 text-sm">
                                    <span class="text-success font-weight-bolder" data-stat="asesmen.new_7_days_formatted">+{{ $asesmenStats['new_7_days'] }}</span>
                                    <span class="text-secondary">7 hari terakhir</span>
                                </p>
                                @else
                                <p class="mb-0 text-sm" id="asesmen-trend" style="display:none;">
                                    <span class="text-success font-weight-bolder" data-stat="asesmen.new_7_days_formatted"></span>
                                    <span class="text-secondary">7 hari terakhir</span>
                                </p>
                                @endif
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-warning shadow text-center border-radius-md">
                                <i class="fas fa-tasks text-lg opacity-10"></i>
                            </div>
                        </div>
                    </div>
                    <hr class="horizontal dark my-3">
                    <div class="d-flex justify-content-between text-xs">
                        <span>
                            <i class="fas fa-check-circle text-success me-1"></i>
                            Selesai: <strong data-stat="asesmen.selesai">{{ $asesmenStats['selesai'] }}</strong>
                        </span>
                        <span>
                            <i class="fas fa-search text-primary me-1"></i>
                            Sampling: <strong data-stat="asesmen.sampled">{{ $asesmenStats['sampled'] }}</strong>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card: Sertifikat --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-uppercase font-weight-bold text-secondary">Sertifikat</p>
                                <h4 class="font-weight-bolder mb-0" data-stat="sertifikat.total">{{ number_format($sertifikatStats['total']) }}</h4>
                                @if($sertifikatStats['terbit_bulan_ini'] > 0)
                                <p class="mb-0 text-sm">
                                    <span class="text-success font-weight-bolder" data-stat="sertifikat.terbit_bulan_ini_formatted">+{{ $sertifikatStats['terbit_bulan_ini'] }}</span>
                                    <span class="text-secondary">bulan ini</span>
                                </p>
                                @else
                                <p class="mb-0 text-sm" id="sertifikat-trend" style="display:none;">
                                    <span class="text-success font-weight-bolder" data-stat="sertifikat.terbit_bulan_ini_formatted"></span>
                                    <span class="text-secondary">bulan ini</span>
                                </p>
                                @endif
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-success shadow text-center border-radius-md">
                                <i class="fas fa-certificate text-lg opacity-10"></i>
                            </div>
                        </div>
                    </div>
                    <hr class="horizontal dark my-3">
                    <div class="d-flex justify-content-between text-xs">
                        <span>
                            <i class="fas fa-check text-success me-1"></i>
                            Aktif: <strong data-stat="sertifikat.aktif">{{ $sertifikatStats['aktif'] }}</strong>
                        </span>
                        <span>
                            <i class="fas fa-times text-danger me-1"></i>
                            Kadaluarsa: <strong data-stat="sertifikat.kadaluarsa">{{ $sertifikatStats['kadaluarsa'] }}</strong>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ================================================================== --}}
    {{-- B. ALUR SERTIFIKASI (FLOW VISUAL) --}}
    {{-- ================================================================== --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="mb-0">
                                <i class="fas fa-route me-2 text-primary"></i>
                                Alur Proses Sertifikasi
                            </h6>
                            <p class="text-xs text-secondary mb-0">
                                Visualisasi jumlah data di setiap tahap proses sertifikasi (ISO 17024 Clause 9)
                            </p>
                        </div>
                    </div>
                </div>
                <div class="card-body pt-4 pb-3">
                    <div class="row align-items-center">
                        {{-- Tahap 1: Pra-Pendaftaran --}}
                        <div class="col text-center">
                            <div class="flow-step">
                                <div class="icon icon-shape bg-gradient-secondary shadow mx-auto mb-2" style="width: 50px; height: 50px;">
                                    <i class="fas fa-user-plus text-white"></i>
                                </div>
                                <h6 class="mb-0">{{ $flowStats['pra_pendaftaran'] }}</h6>
                                <p class="text-xs text-secondary mb-0">Pra-Pendaftaran</p>
                            </div>
                        </div>
                        
                        <div class="col-auto px-0">
                            <i class="fas fa-chevron-right text-secondary"></i>
                        </div>
                        
                        {{-- Tahap 2: Verifikasi Admin --}}
                        <div class="col text-center">
                            <div class="flow-step">
                                <div class="icon icon-shape bg-gradient-info shadow mx-auto mb-2" style="width: 50px; height: 50px;">
                                    <i class="fas fa-user-check text-white"></i>
                                </div>
                                <h6 class="mb-0">{{ $flowStats['verifikasi_admin'] }}</h6>
                                <p class="text-xs text-secondary mb-0">Verifikasi Admin</p>
                            </div>
                        </div>
                        
                        <div class="col-auto px-0">
                            <i class="fas fa-chevron-right text-secondary"></i>
                        </div>
                        
                        {{-- Tahap 3: Asesmen Asesor --}}
                        <div class="col text-center">
                            <div class="flow-step">
                                <div class="icon icon-shape bg-gradient-warning shadow mx-auto mb-2" style="width: 50px; height: 50px;">
                                    <i class="fas fa-clipboard-list text-white"></i>
                                </div>
                                <h6 class="mb-0">{{ $flowStats['asesmen_asesor'] }}</h6>
                                <p class="text-xs text-secondary mb-0">Asesmen Asesor</p>
                            </div>
                        </div>
                        
                        <div class="col-auto px-0">
                            <i class="fas fa-chevron-right text-secondary"></i>
                        </div>
                        
                        {{-- Tahap 4: Keputusan Komite --}}
                        <div class="col text-center">
                            <div class="flow-step">
                                <div class="icon icon-shape bg-gradient-danger shadow mx-auto mb-2" style="width: 50px; height: 50px;">
                                    <i class="fas fa-gavel text-white"></i>
                                </div>
                                <h6 class="mb-0">{{ $flowStats['keputusan_komite'] }}</h6>
                                <p class="text-xs text-secondary mb-0">Keputusan Komite</p>
                            </div>
                        </div>
                        
                        <div class="col-auto px-0">
                            <i class="fas fa-chevron-right text-secondary"></i>
                        </div>
                        
                        {{-- Tahap 5: Sertifikat Terbit --}}
                        <div class="col text-center">
                            <div class="flow-step">
                                <div class="icon icon-shape bg-gradient-success shadow mx-auto mb-2" style="width: 50px; height: 50px;">
                                    <i class="fas fa-award text-white"></i>
                                </div>
                                <h6 class="mb-0">{{ $flowStats['sertifikat_terbit'] }}</h6>
                                <p class="text-xs text-secondary mb-0">Sertifikat Terbit</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ================================================================== --}}
    {{-- C. ALERTS & STATISTIK TAMBAHAN --}}
    {{-- ================================================================== --}}
    <div class="row">
        {{-- Kolom Kiri: Alerts --}}
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header pb-0">
                    <h6 class="mb-0">
                        <i class="fas fa-bell me-2 text-warning"></i>
                        Perhatian Segera
                    </h6>
                    <p class="text-xs text-secondary mb-0">Item yang memerlukan tindakan</p>
                </div>
                <div class="card-body pt-3">
                    @forelse($alerts as $alert)
                    <div class="alert alert-{{ $alert['type'] }} text-white mb-3" role="alert">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="{{ $alert['icon'] }} fa-lg"></i>
                            </div>
                            <div class="flex-grow-1">
                                <strong>{{ $alert['title'] }}</strong>
                                <p class="mb-0 text-sm">{{ $alert['message'] }}</p>
                            </div>
                            <div>
                                <a href="{{ $alert['link'] }}" class="btn btn-sm btn-white mb-0">
                                    {{ $alert['link_text'] }}
                                </a>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-4">
                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                        <h6 class="text-secondary">Semua Terkendali</h6>
                        <p class="text-xs text-secondary mb-0">Tidak ada item yang memerlukan perhatian segera</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Kolom Kanan: Statistik Tambahan --}}
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header pb-0">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-pie me-2 text-info"></i>
                        Ringkasan Kinerja
                    </h6>
                    <p class="text-xs text-secondary mb-0">Indikator kinerja utama LSP</p>
                </div>
                <div class="card-body pt-3">
                    {{-- Tingkat Kelulusan --}}
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-sm font-weight-bold">Tingkat Kelulusan</span>
                            <span class="text-sm font-weight-bold">{{ $additionalStats['tingkat_kelulusan'] }}%</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-gradient-success" role="progressbar" 
                                 style="width: {{ $additionalStats['tingkat_kelulusan'] }}%;" 
                                 aria-valuenow="{{ $additionalStats['tingkat_kelulusan'] }}" 
                                 aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <p class="text-xs text-secondary mt-1 mb-0">
                            {{ $additionalStats['total_kompeten'] }} kompeten dari {{ $additionalStats['total_keputusan'] }} total keputusan
                        </p>
                    </div>

                    {{-- Grid Statistik --}}
                    <div class="row">
                        <div class="col-6 mb-3">
                            <div class="border rounded p-3 text-center">
                                <h4 class="mb-0 text-primary">{{ $additionalStats['skema_aktif'] }}</h4>
                                <p class="text-xs text-secondary mb-0">Skema Aktif</p>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="border rounded p-3 text-center">
                                <h4 class="mb-0 text-info">{{ $additionalStats['pendaftaran_bulan_ini'] }}</h4>
                                <p class="text-xs text-secondary mb-0">Pendaftaran Bulan Ini</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-3 text-center">
                                <h4 class="mb-0 text-warning">{{ $additionalStats['asesmen_bulan_ini'] }}</h4>
                                <p class="text-xs text-secondary mb-0">Asesmen Bulan Ini</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-3 text-center">
                                <h4 class="mb-0 text-success">{{ $sertifikatStats['terbit_bulan_ini'] }}</h4>
                                <p class="text-xs text-secondary mb-0">Sertifikat Bulan Ini</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ================================================================== --}}
    {{-- D. AKTIVITAS SISTEM TERAKHIR --}}
    {{-- ================================================================== --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">
                                <i class="fas fa-history me-2 text-secondary"></i>
                                Aktivitas Sistem Terakhir
                            </h6>
                            <p class="text-xs text-secondary mb-0">10 aktivitas terbaru (Audit Trail)</p>
                        </div>
                        @if(auth()->user()->hasPermission('audit.view'))
                        <a href="{{ route('adminui.audit-log.index') }}" class="btn btn-sm bg-gradient-secondary mb-0">
                            <i class="fas fa-list me-1"></i> Lihat Semua Log
                        </a>
                        @endif
                    </div>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Waktu</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Modul</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Aksi</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">User</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Role</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentActivities as $activity)
                                <tr>
                                    <td>
                                        <div class="d-flex px-3 py-1">
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm">{{ $activity['waktu'] }}</h6>
                                                <p class="text-xs text-secondary mb-0">{{ $activity['waktu_relative'] }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-gradient-secondary">{{ $activity['modul'] }}</span>
                                    </td>
                                    <td>
                                        <p class="text-sm mb-0">{{ Str::limit($activity['aksi'], 60) }}</p>
                                    </td>
                                    <td>
                                        <p class="text-sm font-weight-bold mb-0">{{ $activity['user_name'] }}</p>
                                    </td>
                                    <td>
                                        <span class="text-xs text-secondary">{{ $activity['user_role'] }}</span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <p class="text-secondary mb-0">Belum ada aktivitas tercatat</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ================================================================== --}}
    {{-- E. QUICK ACCESS MENU --}}
    {{-- ================================================================== --}}
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h6 class="mb-0">
                        <i class="fas fa-th-large me-2 text-primary"></i>
                        Akses Cepat
                    </h6>
                    <p class="text-xs text-secondary mb-0">Menu yang sering digunakan</p>
                </div>
                <div class="card-body pt-3">
                    <div class="row">
                        @if(auth()->user()->hasPermission('pra_pendaftaran.view'))
                        <div class="col-lg-2 col-md-4 col-6 mb-3">
                            <a href="{{ route('adminui.pra-pendaftaran.index') }}" class="btn btn-outline-primary w-100 py-3">
                                <i class="fas fa-user-plus fa-lg mb-2 d-block"></i>
                                <span class="text-xs">Pra-Pendaftaran</span>
                            </a>
                        </div>
                        @endif
                        
                        @if(auth()->user()->hasPermission('pendaftaran.view'))
                        <div class="col-lg-2 col-md-4 col-6 mb-3">
                            <a href="{{ route('adminui.pendaftaran-sertifikasi.index') }}" class="btn btn-outline-info w-100 py-3">
                                <i class="fas fa-file-signature fa-lg mb-2 d-block"></i>
                                <span class="text-xs">Pendaftaran</span>
                            </a>
                        </div>
                        @endif
                        
                        @if(auth()->user()->hasPermission('asesmen.view'))
                        <div class="col-lg-2 col-md-4 col-6 mb-3">
                            <a href="{{ route('adminui.asesmen.index') }}" class="btn btn-outline-warning w-100 py-3">
                                <i class="fas fa-tasks fa-lg mb-2 d-block"></i>
                                <span class="text-xs">Asesmen</span>
                            </a>
                        </div>
                        @endif
                        
                        @if(auth()->user()->hasPermission('keputusan.view'))
                        <div class="col-lg-2 col-md-4 col-6 mb-3">
                            <a href="{{ route('adminui.keputusan.index') }}" class="btn btn-outline-danger w-100 py-3">
                                <i class="fas fa-gavel fa-lg mb-2 d-block"></i>
                                <span class="text-xs">Keputusan</span>
                            </a>
                        </div>
                        @endif
                        
                        @if(auth()->user()->hasPermission('sertifikat.view'))
                        <div class="col-lg-2 col-md-4 col-6 mb-3">
                            <a href="{{ route('adminui.sertifikat.index') }}" class="btn btn-outline-success w-100 py-3">
                                <i class="fas fa-certificate fa-lg mb-2 d-block"></i>
                                <span class="text-xs">Sertifikat</span>
                            </a>
                        </div>
                        @endif
                        
                        @if(auth()->user()->hasPermission('audit.view'))
                        <div class="col-lg-2 col-md-4 col-6 mb-3">
                            <a href="{{ route('adminui.audit-log.index') }}" class="btn btn-outline-secondary w-100 py-3">
                                <i class="fas fa-history fa-lg mb-2 d-block"></i>
                                <span class="text-xs">Audit Log</span>
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ================================================================== --}}
    {{-- F. FOOTER INFO --}}
    {{-- ================================================================== --}}
    <div class="row mt-4">
        <div class="col-12">
            <div class="card bg-gradient-dark">
                <div class="card-body p-3">
                    <div class="row align-items-center">
                        <div class="col-lg-8">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-info-circle text-white fa-lg me-3"></i>
                                <div>
                                    <p class="text-white text-sm mb-0">
                                        <strong>Catatan untuk Auditor:</strong> 
                                        Dashboard ini menampilkan kondisi operasional LSP secara real-time. Semua data diambil langsung dari database sistem. 
                                        Alur sertifikasi menunjukkan kontrol poin sesuai ISO 17024:2012 Clause 9 (Certification Process).
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                            <span class="badge bg-white text-dark me-2">
                                <i class="fas fa-shield-alt me-1"></i> ISO 17024
                            </span>
                            <span class="badge bg-white text-dark">
                                <i class="fas fa-check-circle me-1"></i> BNSP Compliant
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('styles')
<style>
    .flow-step {
        transition: transform 0.2s ease;
    }
    .flow-step:hover {
        transform: translateY(-5px);
    }
    .alert {
        border: none;
    }
    .alert-warning {
        background: linear-gradient(310deg, #f5a524 0%, #fb6340 100%);
    }
    .alert-danger {
        background: linear-gradient(310deg, #f5365c 0%, #f56036 100%);
    }
    .alert-info {
        background: linear-gradient(310deg, #11cdef 0%, #1171ef 100%);
    }
    /* Realtime update animations */
    .stat-updated {
        animation: pulse-green 0.5s ease-in-out;
    }
    @keyframes pulse-green {
        0% { background-color: transparent; }
        50% { background-color: rgba(45, 206, 137, 0.2); }
        100% { background-color: transparent; }
    }
    #sync-icon.syncing {
        animation: spin 1s linear infinite;
    }
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    #realtime-status.offline {
        background: linear-gradient(310deg, #f5365c 0%, #f56036 100%) !important;
    }
</style>
@endpush

@push('scripts')
<script>
/**
 * ============================================================================
 * Dashboard Realtime Polling - CertiPro LSP
 * ============================================================================
 * Auto-refresh dashboard statistics every 30 seconds
 * Compliant with ISO 17024:2012 operational monitoring requirements
 */
(function() {
    'use strict';
    
    // Configuration
    const CONFIG = {
        pollInterval: 30000, // 30 seconds
        statsUrl: '{{ route("adminui.dashboard.stats") }}',
        refreshUrl: '{{ route("adminui.dashboard.refresh") }}',
        csrfToken: '{{ csrf_token() }}'
    };
    
    // State
    let pollTimer = null;
    let isPolling = false;
    let consecutiveErrors = 0;
    const MAX_ERRORS = 3;
    
    /**
     * Format number with thousand separator
     */
    function formatNumber(num) {
        return new Intl.NumberFormat('id-ID').format(num);
    }
    
    /**
     * Update DOM element with animation
     */
    function updateStat(selector, value, animate = true) {
        const elements = document.querySelectorAll(`[data-stat="${selector}"]`);
        elements.forEach(el => {
            const currentValue = el.textContent.trim();
            const newValue = String(value);
            
            if (currentValue !== newValue) {
                el.textContent = newValue;
                if (animate) {
                    el.classList.add('stat-updated');
                    setTimeout(() => el.classList.remove('stat-updated'), 500);
                }
            }
        });
    }
    
    /**
     * Update timestamp display
     */
    function updateTimestamp(meta) {
        const textEl = document.getElementById('last-updated-text');
        if (textEl && meta.updated_at_human) {
            textEl.textContent = `Data terakhir diperbarui: ${meta.updated_at_human} ${meta.timezone}`;
        }
    }
    
    /**
     * Set loading state
     */
    function setLoadingState(loading) {
        const syncIcon = document.getElementById('sync-icon');
        const refreshBtn = document.getElementById('btn-refresh-stats');
        
        if (syncIcon) {
            syncIcon.classList.toggle('syncing', loading);
        }
        if (refreshBtn) {
            refreshBtn.disabled = loading;
        }
    }
    
    /**
     * Set connection status
     */
    function setConnectionStatus(online) {
        const statusEl = document.getElementById('realtime-status');
        if (statusEl) {
            if (online) {
                statusEl.classList.remove('offline');
                statusEl.innerHTML = '<i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i>LIVE';
            } else {
                statusEl.classList.add('offline');
                statusEl.innerHTML = '<i class="fas fa-exclamation-triangle me-1" style="font-size: 0.5rem;"></i>OFFLINE';
            }
        }
    }
    
    /**
     * Process stats response and update DOM
     */
    function processStats(data) {
        // Pra-Pendaftaran
        if (data.pra_pendaftaran) {
            updateStat('pra_pendaftaran.total', formatNumber(data.pra_pendaftaran.total));
            updateStat('pra_pendaftaran.menunggu_verifikasi', data.pra_pendaftaran.menunggu_verifikasi);
            updateStat('pra_pendaftaran.diterima', data.pra_pendaftaran.diterima);
            if (data.pra_pendaftaran.new_7_days > 0) {
                updateStat('pra_pendaftaran.new_7_days_formatted', '+' + data.pra_pendaftaran.new_7_days);
            }
        }
        
        // Pendaftaran
        if (data.pendaftaran) {
            updateStat('pendaftaran.total', formatNumber(data.pendaftaran.total));
            updateStat('pendaftaran.siap_asesmen', data.pendaftaran.siap_asesmen);
            updateStat('pendaftaran.menunggu_keputusan', data.pendaftaran.menunggu_keputusan);
            if (data.pendaftaran.new_7_days > 0) {
                updateStat('pendaftaran.new_7_days_formatted', '+' + data.pendaftaran.new_7_days);
            }
        }
        
        // Asesmen
        if (data.asesmen) {
            updateStat('asesmen.total', formatNumber(data.asesmen.total));
            updateStat('asesmen.selesai', data.asesmen.selesai);
            updateStat('asesmen.sampled', data.asesmen.sampled);
            if (data.asesmen.new_7_days > 0) {
                updateStat('asesmen.new_7_days_formatted', '+' + data.asesmen.new_7_days);
            }
        }
        
        // Sertifikat
        if (data.sertifikat) {
            updateStat('sertifikat.total', formatNumber(data.sertifikat.total));
            updateStat('sertifikat.aktif', data.sertifikat.aktif);
            updateStat('sertifikat.kadaluarsa', data.sertifikat.kadaluarsa);
            if (data.sertifikat.terbit_bulan_ini > 0) {
                updateStat('sertifikat.terbit_bulan_ini_formatted', '+' + data.sertifikat.terbit_bulan_ini);
            }
        }
        
        // Flow stats
        if (data.flow) {
            updateStat('flow.pra_pendaftaran', data.flow.pra_pendaftaran);
            updateStat('flow.verifikasi_admin', data.flow.verifikasi_admin);
            updateStat('flow.asesmen_asesor', data.flow.asesmen_asesor);
            updateStat('flow.keputusan_komite', data.flow.keputusan_komite);
            updateStat('flow.sertifikat_terbit', data.flow.sertifikat_terbit);
        }
        
        // Additional stats
        if (data.additional) {
            updateStat('additional.skema_aktif', data.additional.skema_aktif);
            updateStat('additional.tingkat_kelulusan', data.additional.tingkat_kelulusan + '%');
        }
        
        // Meta (timestamp)
        if (data.meta) {
            updateTimestamp(data.meta);
        }
    }
    
    /**
     * Fetch stats from API
     */
    async function fetchStats(forceRefresh = false) {
        if (isPolling) return;
        isPolling = true;
        setLoadingState(true);
        
        try {
            const url = forceRefresh ? CONFIG.refreshUrl : CONFIG.statsUrl;
            const options = {
                method: forceRefresh ? 'POST' : 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': CONFIG.csrfToken
                },
                credentials: 'same-origin'
            };
            
            const response = await fetch(url, options);
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.error) {
                throw new Error(data.error);
            }
            
            processStats(data);
            consecutiveErrors = 0;
            setConnectionStatus(true);
            
        } catch (error) {
            console.error('[Dashboard] Polling error:', error.message);
            consecutiveErrors++;
            
            if (consecutiveErrors >= MAX_ERRORS) {
                setConnectionStatus(false);
            }
        } finally {
            isPolling = false;
            setLoadingState(false);
        }
    }
    
    /**
     * Start polling
     */
    function startPolling() {
        if (pollTimer) return;
        
        // Initial fetch
        fetchStats();
        
        // Set interval
        pollTimer = setInterval(() => fetchStats(), CONFIG.pollInterval);
        
        console.log('[Dashboard] Realtime polling started (interval: ' + (CONFIG.pollInterval/1000) + 's)');
    }
    
    /**
     * Stop polling
     */
    function stopPolling() {
        if (pollTimer) {
            clearInterval(pollTimer);
            pollTimer = null;
            console.log('[Dashboard] Realtime polling stopped');
        }
    }
    
    /**
     * Initialize
     */
    function init() {
        // Start polling when page is visible
        startPolling();
        
        // Handle visibility change (pause when tab is hidden)
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                stopPolling();
            } else {
                startPolling();
            }
        });
        
        // Manual refresh button
        const refreshBtn = document.getElementById('btn-refresh-stats');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => fetchStats(true));
        }
        
        // Cleanup on page unload
        window.addEventListener('beforeunload', stopPolling);
    }
    
    // Start when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
</script>
@endpush
