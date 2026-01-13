@extends('adminui.layouts.auth')
@section('title', 'Pengaturan Email')

@push('styles')
<style>
    .email-settings-container {
        max-width: 1400px;
        margin: 0 auto;
    }

    /* Info Banner */
    .info-banner {
        background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
        border-radius: 12px;
        padding: 16px 24px;
        margin-bottom: 24px;
    }

    .info-banner ul {
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .info-banner li {
        color: #fff;
        font-size: 14px;
        padding: 4px 0;
        display: flex;
        align-items: center;
    }

    .info-banner li::before {
        content: "•";
        color: #22c55e;
        font-weight: bold;
        margin-right: 12px;
        font-size: 18px;
    }

    /* Main Grid */
    .settings-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 24px;
    }

    @media (min-width: 1024px) {
        .settings-grid {
            grid-template-columns: 2fr 1fr;
            align-items: start;
        }
    }

    /* Cards */
    .card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        margin-bottom: 20px;
    }

    .card:last-child {
        margin-bottom: 0;
    }

    .card-header {
        padding: 16px 20px;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .card-header-icon {
        width: 20px;
        height: 20px;
        color: #6b7280;
    }

    .card-header-content h3 {
        margin: 0;
        font-size: 15px;
        font-weight: 600;
        color: #1f2937;
    }

    .card-header-content p {
        margin: 2px 0 0;
        font-size: 13px;
        color: #6b7280;
    }

    .card-body {
        padding: 20px;
    }

    /* Form */
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        margin-bottom: 16px;
    }

    @media (max-width: 640px) {
        .form-row {
            grid-template-columns: 1fr;
        }
    }

    .form-group {
        margin-bottom: 0;
    }

    .form-label {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        font-weight: 500;
        color: #374151;
        margin-bottom: 8px;
    }

    .form-label svg {
        width: 14px;
        height: 14px;
        color: #9ca3af;
    }

    .form-label .required {
        color: #ef4444;
    }

    .form-control {
        width: 100%;
        padding: 10px 14px;
        font-size: 14px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        background: #fff;
        transition: all 0.15s;
    }

    .form-control:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .form-control::placeholder {
        color: #9ca3af;
    }

    .form-hint {
        font-size: 12px;
        color: #6b7280;
        margin-top: 6px;
    }

    /* Password Toggle */
    .input-password-wrapper {
        position: relative;
    }

    .input-password-wrapper .form-control {
        padding-right: 44px;
    }

    .toggle-password-btn {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        padding: 4px;
        cursor: pointer;
        color: #9ca3af;
    }

    .toggle-password-btn:hover {
        color: #6b7280;
    }

    .toggle-password-btn svg {
        width: 18px;
        height: 18px;
    }

    /* Action Buttons */
    .action-buttons {
        display: flex;
        gap: 12px;
        margin-top: 24px;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 10px 24px;
        font-size: 14px;
        font-weight: 600;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.15s;
        text-decoration: none;
    }

    .btn svg {
        width: 16px;
        height: 16px;
    }

    .btn-primary {
        background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
        color: #fff;
        border: none;
    }

    .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(14, 165, 233, 0.35);
    }

    .btn-secondary {
        background: #fff;
        color: #374151;
        border: 1px solid #d1d5db;
    }

    .btn-secondary:hover {
        background: #f9fafb;
        border-color: #9ca3af;
    }

    /* Sidebar */
    .sidebar-column {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    /* Test Email Card */
    .test-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        overflow: hidden;
    }

    .test-card .card-header {
        background: #f9fafb;
    }

    .test-card .card-body {
        padding: 16px;
    }

    .btn-test {
        width: 100%;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: #fff;
        border: none;
        padding: 12px 20px;
        font-size: 14px;
        font-weight: 600;
        border-radius: 8px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        margin-top: 12px;
        transition: all 0.15s;
    }

    .btn-test:hover:not(:disabled) {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.35);
    }

    .btn-test:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .btn-test svg {
        width: 16px;
        height: 16px;
    }

    .test-warning {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 12px;
        padding: 10px 12px;
        background: rgba(239, 68, 68, 0.08);
        border-radius: 8px;
        font-size: 13px;
        color: #dc2626;
    }

    .test-warning svg {
        width: 16px;
        height: 16px;
        flex-shrink: 0;
    }

    /* Panduan Cepat */
    .guide-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 16px;
    }

    .guide-title {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 15px;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 16px;
    }

    .guide-title svg {
        width: 20px;
        height: 20px;
        color: #eab308;
    }

    .guide-section {
        margin-bottom: 14px;
    }

    .guide-section:last-child {
        margin-bottom: 0;
    }

    .guide-provider {
        font-weight: 600;
        font-size: 14px;
        margin-bottom: 4px;
    }

    .guide-provider.gmail { color: #4285f4; }
    .guide-provider.mailtrap { color: #22bc66; }
    .guide-provider.mailgun { color: #f06b66; }

    .guide-section ul {
        margin: 0;
        padding-left: 16px;
        font-size: 13px;
        color: #6b7280;
    }

    .guide-section li {
        margin-bottom: 2px;
    }

    /* Audit Ready */
    .audit-box {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        border-radius: 12px;
        padding: 16px;
        color: #fff;
    }

    .audit-box-header {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 15px;
        font-weight: 600;
        margin-bottom: 8px;
    }

    .audit-box-header svg {
        width: 20px;
        height: 20px;
    }

    .audit-box p {
        font-size: 13px;
        opacity: 0.9;
        margin: 0;
        line-height: 1.5;
    }

    /* Alerts */
    .alert {
        padding: 14px 16px;
        border-radius: 8px;
        margin-bottom: 16px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }

    .alert svg {
        width: 20px;
        height: 20px;
        flex-shrink: 0;
    }

    .alert-success {
        background: rgba(34, 197, 94, 0.1);
        border: 1px solid rgba(34, 197, 94, 0.2);
        color: #16a34a;
    }

    .alert-error {
        background: rgba(239, 68, 68, 0.1);
        border: 1px solid rgba(239, 68, 68, 0.2);
        color: #dc2626;
    }

    .alert-content strong {
        display: block;
        font-size: 14px;
        margin-bottom: 4px;
    }

    .alert-content p {
        font-size: 13px;
        margin: 0;
    }

    .alert-content ul {
        font-size: 13px;
        margin: 4px 0 0;
        padding-left: 20px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="email-settings-container">

        {{-- Configuration Status Banner --}}
        @if($isConfigured)
        <div class="alert alert-success" style="margin-bottom: 16px;">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/>
            </svg>
            <div class="alert-content">
                <strong>Email Sudah Dikonfigurasi</strong>
                <p>Konfigurasi SMTP sudah tersimpan. Sistem siap mengirim email notifikasi.</p>
            </div>
        </div>
        @else
        <div class="alert" style="background: rgba(234, 179, 8, 0.1); border: 1px solid rgba(234, 179, 8, 0.3); color: #b45309; margin-bottom: 16px;">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3"/><path d="M12 9v4"/><path d="M12 17h.01"/>
            </svg>
            <div class="alert-content">
                <strong>Email Belum Dikonfigurasi</strong>
                <p>Silakan lengkapi konfigurasi SMTP agar sistem dapat mengirim email notifikasi.</p>
            </div>
        </div>
        @endif

        {{-- Info Banner --}}
        <div class="info-banner">
            <ul>
                <li>Password SMTP dienkripsi dan disimpan dengan aman</li>
                <li>Semua perubahan dicatat ke Audit Log untuk kepatuhan BNSP/ISO 17024</li>
                <li>Gunakan tombol "Test Email" untuk memverifikasi konfigurasi</li>
            </ul>
        </div>

        {{-- Alerts --}}
        @if(session('success'))
        <div class="alert alert-success">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/>
            </svg>
            <div class="alert-content">
                <strong>Berhasil!</strong>
                <p>{{ session('success') }}</p>
            </div>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-error">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/>
            </svg>
            <div class="alert-content">
                <strong>Error!</strong>
                <p>{{ session('error') }}</p>
            </div>
        </div>
        @endif

        @if($errors->any())
        <div class="alert alert-error">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3"/>
            </svg>
            <div class="alert-content">
                <strong>Validasi Error:</strong>
                <ul>
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif

        {{-- Main Grid --}}
        <div class="settings-grid">

            {{-- Main Column --}}
            <div class="main-column">
                <form action="{{ route('adminui.settings.email.store') }}" method="POST" id="emailSettingsForm">
                    @csrf

                    {{-- Konfigurasi SMTP --}}
                    <div class="card">
                        <div class="card-header">
                            <svg class="card-header-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect width="20" height="8" x="2" y="2" rx="2" ry="2"/><rect width="20" height="8" x="2" y="14" rx="2" ry="2"/><line x1="6" x2="6.01" y1="6" y2="6"/><line x1="6" x2="6.01" y1="18" y2="18"/>
                            </svg>
                            <div class="card-header-content">
                                <h3>Konfigurasi SMTP</h3>
                                <p>Atur server SMTP untuk pengiriman email notifikasi</p>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                                        </svg>
                                        Mail Driver <span class="required">*</span>
                                    </label>
                                    <select name="mail_driver" class="form-control" required>
                                        @foreach($fields['mail_driver']['options'] as $option)
                                        <option value="{{ $option }}" {{ ($settings['mail_driver'] ?? 'smtp') == $option ? 'selected' : '' }}>
                                            {{ strtoupper($option) }}
                                        </option>
                                        @endforeach
                                    </select>
                                    <div class="form-hint">Pilih SMTP untuk sebagian besar kasus</div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/>
                                        </svg>
                                        Encryption <span class="required">*</span>
                                    </label>
                                    <select name="mail_encryption" class="form-control" required>
                                        <option value="tls" {{ ($settings['mail_encryption'] ?? 'tls') == 'tls' ? 'selected' : '' }}>TLS (Recommended)</option>
                                        <option value="ssl" {{ ($settings['mail_encryption'] ?? '') == 'ssl' ? 'selected' : '' }}>SSL</option>
                                        <option value="null" {{ ($settings['mail_encryption'] ?? '') == 'null' ? 'selected' : '' }}>None</option>
                                    </select>
                                    <div class="form-hint">TLS direkomendasikan untuk keamanan</div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect width="20" height="8" x="2" y="2" rx="2" ry="2"/><rect width="20" height="8" x="2" y="14" rx="2" ry="2"/><line x1="6" x2="6.01" y1="6" y2="6"/><line x1="6" x2="6.01" y1="18" y2="18"/>
                                        </svg>
                                        SMTP Host <span class="required">*</span>
                                    </label>
                                    <input type="text" name="mail_host" class="form-control" value="{{ $settings['mail_host'] ?? '' }}" placeholder="smtp.gmail.com" required>
                                    <div class="form-hint">Contoh: smtp.gmail.com, smtp.mailtrap.io</div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                                        </svg>
                                        Port <span class="required">*</span>
                                    </label>
                                    <input type="number" name="mail_port" class="form-control" value="{{ $settings['mail_port'] ?? 587 }}" placeholder="587" min="1" max="65535" required>
                                    <div class="form-hint">587 (TLS) atau 465 (SSL)</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Kredensial SMTP --}}
                    <div class="card">
                        <div class="card-header">
                            <svg class="card-header-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m21 2-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0 3 3L22 7l-3-3m-3.5 3.5L19 4"/>
                            </svg>
                            <div class="card-header-content">
                                <h3>Kredensial SMTP</h3>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                                        </svg>
                                        Username
                                    </label>
                                    <input type="text" name="mail_username" class="form-control" value="{{ $settings['mail_username'] ?? '' }}" placeholder="email@example.com" autocomplete="off">
                                    <div class="form-hint">Email atau username untuk login SMTP</div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                        </svg>
                                        Password
                                    </label>
                                    <div class="input-password-wrapper">
                                        <input type="password" name="mail_password" id="mail_password" class="form-control" placeholder="Password SMTP" autocomplete="new-password">
                                        <button type="button" class="toggle-password-btn" onclick="togglePassword()">
                                            <svg id="toggleIcon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="form-hint">Kosongkan jika tidak ingin mengubah password</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Pengirim Email --}}
                    <div class="card">
                        <div class="card-header">
                            <svg class="card-header-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/>
                            </svg>
                            <div class="card-header-content">
                                <h3>Pengirim Email</h3>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="4"/><path d="M16 8v5a3 3 0 0 0 6 0v-1a10 10 0 1 0-4 8"/>
                                        </svg>
                                        From Address <span class="required">*</span>
                                    </label>
                                    <input type="email" name="mail_from_address" class="form-control" value="{{ $settings['mail_from_address'] ?? '' }}" placeholder="noreply@lsp.id" required>
                                    <div class="form-hint">Alamat email pengirim</div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                                        </svg>
                                        From Name <span class="required">*</span>
                                    </label>
                                    <input type="text" name="mail_from_name" class="form-control" value="{{ $settings['mail_from_name'] ?? '' }}" placeholder="{{ systemCompanyName() ?? 'LSP' }}" required>
                                    <div class="form-hint">Nama yang tampil di email</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="action-buttons">
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>
                            </svg>
                            SIMPAN PENGATURAN
                        </button>
                        <a href="{{ route('adminui.dashboard') }}" class="btn btn-secondary">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m12 19-7-7 7-7"/><path d="M19 12H5"/>
                            </svg>
                            KEMBALI
                        </a>
                    </div>
                </form>
            </div>

            {{-- Sidebar Column --}}
            <div class="sidebar-column">

                {{-- Test Email --}}
                <div class="test-card">
                    <div class="card-header">
                        <svg class="card-header-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/>
                        </svg>
                        <div class="card-header-content">
                            <h3>Test Email</h3>
                            <p>Kirim email test untuk verifikasi</p>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label class="form-label">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                                </svg>
                                Kirim ke Email
                            </label>
                            <input type="email" id="test_email_input" class="form-control" value="{{ auth()->user()->email ?? '' }}" placeholder="test@example.com" {{ !$isConfigured ? 'disabled' : '' }}>
                        </div>

                        <button type="button" class="btn-test" id="testEmailBtn" {{ !$isConfigured ? 'disabled' : '' }} onclick="sendTestEmail()">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/>
                            </svg>
                            KIRIM TEST EMAIL
                        </button>

                        @if(!$isConfigured)
                        <div class="test-warning">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/>
                            </svg>
                            Simpan konfigurasi terlebih dahulu
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Panduan Cepat --}}
                <div class="guide-card">
                    <div class="guide-title">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M15 14c.2-1 .7-1.7 1.5-2.5 1-.9 1.5-2.2 1.5-3.5A6 6 0 0 0 6 8c0 1 .2 2.2 1.5 3.5.7.7 1.3 1.5 1.5 2.5"/><path d="M9 18h6"/><path d="M10 22h4"/>
                        </svg>
                        Panduan Cepat
                    </div>

                    <div class="guide-section">
                        <div class="guide-provider gmail">Gmail:</div>
                        <ul>
                            <li>Host: smtp.gmail.com</li>
                            <li>Port: 587 (TLS)</li>
                            <li>Gunakan App Password</li>
                        </ul>
                    </div>

                    <div class="guide-section">
                        <div class="guide-provider mailtrap">Mailtrap:</div>
                        <ul>
                            <li>Host: sandbox.smtp.mailtrap.io</li>
                            <li>Port: 2525</li>
                            <li>Untuk testing</li>
                        </ul>
                    </div>

                    <div class="guide-section">
                        <div class="guide-provider mailgun">Mailgun:</div>
                        <ul>
                            <li>Host: smtp.mailgun.org</li>
                            <li>Port: 587 (TLS)</li>
                            <li>Production ready</li>
                        </ul>
                    </div>
                </div>

                {{-- Audit Ready --}}
                <div class="audit-box">
                    <div class="audit-box-header">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/>
                        </svg>
                        Audit Ready
                    </div>
                    <p>Semua perubahan pengaturan email dicatat ke Audit Log</p>
                </div>

            </div>

        </div>

    </div>
</div>

{{-- Hidden Form for Test Email --}}
<form action="{{ route('adminui.settings.email.test') }}" method="POST" id="testEmailForm" style="display: none;">
    @csrf
    <input type="email" name="test_email" id="hidden_test_email">
</form>
@endsection

@push('scripts')
<script>
function togglePassword() {
    const input = document.getElementById('mail_password');
    const icon = document.getElementById('toggleIcon');

    if (input.type === 'password') {
        input.type = 'text';
        icon.innerHTML = '<path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/>';
    } else {
        input.type = 'password';
        icon.innerHTML = '<path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>';
    }
}

document.getElementById('emailSettingsForm').addEventListener('submit', function(e) {
    const btn = document.getElementById('submitBtn');
    btn.innerHTML = '<svg class="spin" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg> Menyimpan...';
    btn.disabled = true;
});

function sendTestEmail() {
    const email = document.getElementById('test_email_input').value;
    if (!email) {
        alert('Masukkan alamat email tujuan');
        return;
    }

    const btn = document.getElementById('testEmailBtn');
    btn.innerHTML = '<svg class="spin" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg> Mengirim...';
    btn.disabled = true;

    document.getElementById('hidden_test_email').value = email;
    document.getElementById('testEmailForm').submit();
}
</script>

<style>
@keyframes spin {
    to { transform: rotate(360deg); }
}
.spin {
    animation: spin 1s linear infinite;
}
</style>
@endpush
