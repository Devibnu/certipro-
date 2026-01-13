@extends('adminui.layouts.auth')
@section('title', 'Branding & Logo')

@push('styles')
<style>
    .branding-settings-container {
        max-width: 1200px;
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
    .form-group {
        margin-bottom: 20px;
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

    /* File Upload */
    .upload-area {
        border: 2px dashed #d1d5db;
        border-radius: 12px;
        padding: 32px;
        text-align: center;
        transition: all 0.2s;
        cursor: pointer;
        background: #f9fafb;
    }

    .upload-area:hover {
        border-color: #3b82f6;
        background: #f0f9ff;
    }

    .upload-area.dragging {
        border-color: #3b82f6;
        background: #dbeafe;
    }

    .upload-icon {
        width: 48px;
        height: 48px;
        margin: 0 auto 16px;
        color: #6b7280;
    }

    .upload-text {
        font-size: 14px;
        color: #374151;
        margin-bottom: 4px;
    }

    .upload-hint {
        font-size: 12px;
        color: #9ca3af;
    }

    .file-input {
        display: none;
    }

    /* Preview */
    .logo-preview-container {
        margin-top: 20px;
        padding: 20px;
        background: #f9fafb;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
    }

    .logo-preview {
        width: 100%;
        max-width: 300px;
        height: auto;
        border-radius: 8px;
        margin: 0 auto;
        display: block;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .preview-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 12px;
        justify-content: center;
    }

    .preview-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        font-size: 12px;
        font-weight: 500;
        border-radius: 6px;
        background: #fff;
        border: 1px solid #e5e7eb;
        color: #6b7280;
    }

    .preview-badge svg {
        width: 14px;
        height: 14px;
    }

    .preview-badge.active {
        background: rgba(34, 197, 94, 0.1);
        border-color: rgba(34, 197, 94, 0.2);
        color: #16a34a;
    }

    /* Usage Info Card */
    .usage-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .usage-list li {
        padding: 10px 0;
        border-bottom: 1px solid #f3f4f6;
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 13px;
        color: #374151;
    }

    .usage-list li:last-child {
        border-bottom: none;
    }

    .usage-list svg {
        width: 16px;
        height: 16px;
        color: #10b981;
        flex-shrink: 0;
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
        border: none;
    }

    .btn svg {
        width: 16px;
        height: 16px;
    }

    .btn-primary {
        background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
        color: #fff;
    }

    .btn-primary:hover:not(:disabled) {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(14, 165, 233, 0.35);
    }

    .btn-danger {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: #fff;
    }

    .btn-danger:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.35);
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

    .btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    /* Alerts */
    .alert {
        padding: 16px;
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

    .alert-info {
        background: rgba(14, 165, 233, 0.1);
        border: 1px solid rgba(14, 165, 233, 0.2);
        color: #0284c7;
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

    /* Info Stats */
    .info-stats {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        margin-top: 16px;
    }

    .info-stat {
        padding: 12px;
        background: #f9fafb;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
    }

    .info-stat-label {
        font-size: 11px;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
    }

    .info-stat-value {
        font-size: 13px;
        color: #1f2937;
        font-weight: 500;
    }

    /* Current Logo Display */
    .current-logo-display {
        padding: 20px;
        background: #f9fafb;
        border-radius: 12px;
        text-align: center;
        border: 1px solid #e5e7eb;
    }

    .current-logo-display img {
        max-width: 200px;
        max-height: 120px;
        width: auto;
        height: auto;
        margin: 0 auto;
        display: block;
    }

    .no-logo-placeholder {
        padding: 40px 20px;
        color: #9ca3af;
        font-size: 14px;
    }

    .no-logo-placeholder svg {
        width: 48px;
        height: 48px;
        margin: 0 auto 12px;
        color: #d1d5db;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="branding-settings-container">

        {{-- Info Banner --}}
        <div class="info-banner">
            <ul>
                <li>Logo otomatis digunakan di Admin Panel, Landing Page, Sertifikat PDF, dan Audit Evidence</li>
                <li>Format yang didukung: PNG, JPG, JPEG, SVG (Maksimal 2MB)</li>
                <li>Semua perubahan dicatat ke Audit Log untuk kepatuhan BNSP/ISO 17024</li>
            </ul>
        </div>

        {{-- SweetAlert for Success/Error --}}
        @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: '{{ session('success') }}',
                    confirmButtonColor: '#10b981'
                });
            });
        </script>
        @endif

        @if(session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: '{{ session('error') }}',
                    confirmButtonColor: '#ef4444'
                });
            });
        </script>
        @endif

        @if($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Validasi Error',
                    html: '{!! implode("<br>", $errors->all()) !!}',
                    confirmButtonColor: '#ef4444'
                });
            });
        </script>
        @endif

        {{-- Alerts (visible backup) --}}
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

        @if(session('info'))
        <div class="alert alert-info">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/>
            </svg>
            <div class="alert-content">
                <strong>Info</strong>
                <p>{{ session('info') }}</p>
            </div>
        </div>
        @endif

        {{-- Main Grid --}}
        <div class="settings-grid">

            {{-- Main Column --}}
            <div>
                {{-- Upload Logo Card --}}
                <div class="card">
                    <div class="card-header">
                        <svg class="card-header-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect width="18" height="18" x="3" y="3" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>
                        </svg>
                        <div class="card-header-content">
                            <h3>Upload Logo Sistem</h3>
                            <p>Upload logo baru untuk sistem LSP</p>
                        </div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('adminui.settings.branding.update') }}" method="POST" enctype="multipart/form-data" id="brandingForm">
                            @csrf

                            {{-- Logo Upload --}}
                            <div class="form-group">
                                <label class="form-label">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect width="18" height="18" x="3" y="3" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>
                                    </svg>
                                    Logo <span class="required">*</span>
                                </label>
                                <div class="upload-area" id="uploadArea">
                                    <svg class="upload-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/>
                                    </svg>
                                    <div class="upload-text">Klik untuk upload atau drag & drop</div>
                                    <div class="upload-hint">PNG, JPG, JPEG, SVG (Max. 2MB)</div>
                                </div>
                                <input type="file" name="logo" id="logoInput" class="file-input" accept="image/png,image/jpeg,image/jpg,image/svg+xml">
                                <div class="form-hint">Rekomendasi: Logo dengan background transparan untuk hasil terbaik</div>
                                
                                {{-- Preview --}}
                                <div class="logo-preview-container" id="previewContainer" style="display:none;">
                                    <img id="logoPreview" class="logo-preview" src="" alt="Logo Preview">
                                    <div class="preview-badges">
                                        <div class="preview-badge active">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/>
                                            </svg>
                                            Digunakan di PDF
                                        </div>
                                        <div class="preview-badge active">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/>
                                            </svg>
                                            Digunakan di Sertifikat
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Nama Perusahaan --}}
                            <div class="form-group">
                                <label class="form-label">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M3 9h18v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9Z"/><path d="m3 9 2.45-4.9A2 2 0 0 1 7.24 3h9.52a2 2 0 0 1 1.8 1.1L21 9"/><path d="M12 3v6"/>
                                    </svg>
                                    Nama LSP (Opsional)
                                </label>
                                <input type="text" name="nama_perusahaan" class="form-control" 
                                       placeholder="Contoh: LSP Teknologi Indonesia" 
                                       value="{{ old('nama_perusahaan', $logoInfo['nama_perusahaan'] ?? '') }}">
                                <div class="form-hint">Kosongkan jika logo sudah memiliki nama. Nama ini untuk dokumen resmi.</div>
                            </div>

                            {{-- Tagline --}}
                            <div class="form-group">
                                <label class="form-label">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M4 7V4h16v3"/><path d="M9 20h6"/><path d="M12 4v16"/>
                                    </svg>
                                    Tagline (Opsional)
                                </label>
                                <input type="text" name="tagline" class="form-control" 
                                       placeholder="Contoh: Profesional • Terpercaya • Bersertifikat"
                                       value="{{ old('tagline', $logoInfo['tagline'] ?? '') }}">
                                <div class="form-hint">Slogan atau tagline lembaga (ditampilkan di beberapa dokumen)</div>
                            </div>

                            {{-- Action Buttons --}}
                            <div class="action-buttons">
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>
                                    </svg>
                                    Simpan & Terapkan
                                </button>
                                <a href="{{ route('adminui.dashboard') }}" class="btn btn-secondary">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="m12 19-7-7 7-7"/><path d="M19 12H5"/>
                                    </svg>
                                    Batal
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Sidebar Column --}}
            <div class="sidebar-column">

                {{-- Current Logo Card --}}
                <div class="card">
                    <div class="card-header">
                        <svg class="card-header-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/>
                        </svg>
                        <div class="card-header-content">
                            <h3>Logo Saat Ini</h3>
                            <p>Logo yang sedang aktif</p>
                        </div>
                    </div>
                    <div class="card-body">
                        @if($logoInfo['exists'])
                            <div class="current-logo-display">
                                <img src="{{ $logoInfo['path'] }}" alt="Current Logo">
                            </div>
                            
                            <div class="info-stats">
                                <div class="info-stat">
                                    <div class="info-stat-label">Nama LSP</div>
                                    <div class="info-stat-value">{{ $logoInfo['nama_perusahaan'] }}</div>
                                </div>
                                <div class="info-stat">
                                    <div class="info-stat-label">Terakhir Update</div>
                                    <div class="info-stat-value">{{ $logoInfo['updated_at'] }}</div>
                                </div>
                            </div>

                            {{-- Delete Button --}}
                            <form action="{{ route('adminui.settings.branding.destroy') }}" method="POST" style="margin-top: 16px;" onsubmit="return confirm('Yakin ingin menghapus logo? Logo akan dihapus dari semua dokumen.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" style="width: 100%;">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                                    </svg>
                                    Hapus Logo
                                </button>
                            </form>
                        @else
                            <div class="current-logo-display">
                                <div class="no-logo-placeholder">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect width="18" height="18" x="3" y="3" rx="2"/><path d="m3 3 18 18"/>
                                    </svg>
                                    <div>Belum ada logo</div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Usage Info Card --}}
                <div class="card">
                    <div class="card-header">
                        <svg class="card-header-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/>
                        </svg>
                        <div class="card-header-content">
                            <h3>Penggunaan Logo</h3>
                            <p>Logo diterapkan otomatis di:</p>
                        </div>
                    </div>
                    <div class="card-body">
                        <ul class="usage-list">
                            <li>
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/>
                                </svg>
                                Sidebar Admin Panel
                            </li>
                            <li>
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/>
                                </svg>
                                Top Navbar Admin
                            </li>
                            <li>
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/>
                                </svg>
                                Landing Page Header & Footer
                            </li>
                            <li>
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/>
                                </svg>
                                Sertifikat Kompetensi (PDF)
                            </li>
                            <li>
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/>
                                </svg>
                                Audit Evidence (PDF)
                            </li>
                            <li>
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/>
                                </svg>
                                Email Notifikasi
                            </li>
                        </ul>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const uploadArea = document.getElementById('uploadArea');
    const logoInput = document.getElementById('logoInput');
    const previewContainer = document.getElementById('previewContainer');
    const logoPreview = document.getElementById('logoPreview');
    const submitBtn = document.getElementById('submitBtn');

    // Click to upload
    uploadArea.addEventListener('click', () => logoInput.click());

    // File input change
    logoInput.addEventListener('change', function(e) {
        handleFile(e.target.files[0]);
    });

    // Drag & Drop
    uploadArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadArea.classList.add('dragging');
    });

    uploadArea.addEventListener('dragleave', () => {
        uploadArea.classList.remove('dragging');
    });

    uploadArea.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadArea.classList.remove('dragging');
        const file = e.dataTransfer.files[0];
        if (file && file.type.startsWith('image/')) {
            logoInput.files = e.dataTransfer.files;
            handleFile(file);
        }
    });

    function handleFile(file) {
        if (!file) return;

        // Validate file type
        const validTypes = ['image/png', 'image/jpeg', 'image/jpg', 'image/svg+xml'];
        if (!validTypes.includes(file.type)) {
            alert('Format file tidak didukung. Gunakan PNG, JPG, JPEG, atau SVG.');
            return;
        }

        // Validate file size (2MB)
        if (file.size > 2 * 1024 * 1024) {
            alert('Ukuran file terlalu besar. Maksimal 2MB.');
            return;
        }

        // Show preview
        const reader = new FileReader();
        reader.onload = function(e) {
            logoPreview.src = e.target.result;
            previewContainer.style.display = 'block';
        };
        reader.readAsDataURL(file);
    }

    // Form submission with SweetAlert2
    document.getElementById('brandingForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const form = this;
        const logoInput = document.getElementById('logoInput');
        const hasExistingLogo = {{ $logoInfo['exists'] ? 'true' : 'false' }};
        
        // Check if logo is required (no existing logo and no new file selected)
        if (!hasExistingLogo && (!logoInput.files || logoInput.files.length === 0)) {
            Swal.fire({
                icon: 'warning',
                title: 'Logo Diperlukan',
                text: 'Silakan upload logo terlebih dahulu.',
                confirmButtonColor: '#0ea5e9'
            });
            return;
        }
        
        // Show loading popup
        Swal.fire({
            title: 'Menyimpan...',
            html: 'Mohon tunggu, logo sedang diproses.',
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        // Disable submit button
        submitBtn.disabled = true;
        
        // Submit form
        form.submit();
    });
});
</script>
@endpush
@endsection
