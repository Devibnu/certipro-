<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paket Audit Sistem Informasi Sertifikasi - {{ $lsp['nama'] ?? 'LSP' }}</title>
    <style>
        /* ========================================================
           CSS Styles for Final Audit Package PDF
           ======================================================== */
        
        @page {
            margin: 20mm 15mm 20mm 15mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.5;
            color: #1a1a1a;
            background: #fff;
        }

        /* Watermark */
        .watermark {
            position: fixed;
            top: 45%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 36pt;
            color: rgba(0, 0, 0, 0.06);
            font-weight: bold;
            letter-spacing: 3px;
            white-space: nowrap;
            z-index: 0;
            pointer-events: none;
        }

        /* =====================================================
           COVER PAGE
           ===================================================== */
        .cover-page {
            position: relative;
            min-height: 100%;
            display: flex;
            flex-direction: column;
            text-align: center;
            page-break-after: always;
        }

        .cover-header {
            padding: 30px 0 20px;
            border-bottom: 3px double #0d6efd;
        }

        .cover-logo {
            font-size: 28pt;
            font-weight: bold;
            color: #0d6efd;
            margin-bottom: 5px;
        }

        .cover-lsp-name {
            font-size: 14pt;
            color: #495057;
            font-weight: 600;
        }

        .cover-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 40px 20px;
        }

        .cover-title {
            font-size: 22pt;
            font-weight: bold;
            color: #1a1a1a;
            margin-bottom: 10px;
            line-height: 1.3;
        }

        .cover-subtitle {
            font-size: 14pt;
            color: #495057;
            margin-bottom: 40px;
        }

        .cover-info-box {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 25px;
            margin: 0 auto;
            max-width: 450px;
            text-align: left;
        }

        .cover-info-row {
            display: table;
            width: 100%;
            margin-bottom: 8px;
        }

        .cover-info-row:last-child {
            margin-bottom: 0;
        }

        .cover-info-label {
            display: table-cell;
            width: 40%;
            font-weight: 600;
            color: #495057;
            padding-right: 10px;
        }

        .cover-info-value {
            display: table-cell;
            color: #1a1a1a;
        }

        .cover-footer {
            padding: 20px 0;
            border-top: 1px solid #dee2e6;
        }

        .cover-confidential {
            background: #dc3545;
            color: #fff;
            font-size: 9pt;
            font-weight: bold;
            padding: 8px 20px;
            display: inline-block;
            border-radius: 4px;
            letter-spacing: 1px;
        }

        .cover-compliance {
            margin-top: 15px;
            font-size: 9pt;
            color: #6c757d;
        }

        /* =====================================================
           CONTENT PAGES
           ===================================================== */
        .page {
            position: relative;
            page-break-after: always;
        }

        .page:last-child {
            page-break-after: auto;
        }

        /* Page Header */
        .page-header {
            display: table;
            width: 100%;
            border-bottom: 2px solid #0d6efd;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .page-header-left {
            display: table-cell;
            vertical-align: middle;
        }

        .page-header-right {
            display: table-cell;
            text-align: right;
            vertical-align: middle;
            font-size: 8pt;
            color: #6c757d;
        }

        .page-header-logo {
            font-size: 12pt;
            font-weight: bold;
            color: #0d6efd;
        }

        .page-header-title {
            font-size: 8pt;
            color: #6c757d;
        }

        /* Section Styling */
        .section {
            margin-bottom: 25px;
        }

        .section-title {
            font-size: 14pt;
            font-weight: bold;
            color: #0d6efd;
            border-bottom: 2px solid #0d6efd;
            padding-bottom: 8px;
            margin-bottom: 15px;
        }

        .section-number {
            display: inline-block;
            background: #0d6efd;
            color: #fff;
            width: 28px;
            height: 28px;
            line-height: 28px;
            text-align: center;
            border-radius: 50%;
            margin-right: 10px;
            font-size: 12pt;
        }

        .subsection-title {
            font-size: 11pt;
            font-weight: bold;
            color: #495057;
            margin-bottom: 10px;
            margin-top: 15px;
        }

        /* Content Box */
        .content-box {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 15px;
        }

        .content-box.highlight {
            background: #e7f1ff;
            border-color: #b6d4fe;
        }

        /* Info Grid */
        .info-grid {
            display: table;
            width: 100%;
        }

        .info-row {
            display: table-row;
        }

        .info-label {
            display: table-cell;
            width: 35%;
            padding: 6px 10px 6px 0;
            font-weight: 600;
            color: #495057;
            vertical-align: top;
        }

        .info-value {
            display: table-cell;
            padding: 6px 0;
            color: #1a1a1a;
        }

        /* Statistics Grid */
        .stats-grid {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }

        .stats-item {
            display: table-cell;
            text-align: center;
            padding: 10px;
            background: #e7f1ff;
            border-right: 2px solid #fff;
        }

        .stats-item:last-child {
            border-right: none;
        }

        .stats-number {
            font-size: 20pt;
            font-weight: bold;
            color: #0d6efd;
        }

        .stats-label {
            font-size: 8pt;
            color: #495057;
            margin-top: 3px;
        }

        /* Table Styling */
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 9pt;
        }

        .table th {
            background: #0d6efd;
            color: #fff;
            font-weight: 600;
            padding: 10px 8px;
            text-align: left;
            border: 1px solid #0d6efd;
        }

        .table td {
            padding: 8px;
            border: 1px solid #dee2e6;
            vertical-align: top;
        }

        .table tr:nth-child(even) td {
            background: #f8f9fa;
        }

        .table-sm td,
        .table-sm th {
            padding: 6px;
            font-size: 8pt;
        }

        /* Module List */
        .module-list {
            list-style: none;
            padding: 0;
        }

        .module-item {
            padding: 8px 0;
            border-bottom: 1px dashed #dee2e6;
        }

        .module-item:last-child {
            border-bottom: none;
        }

        .module-name {
            font-weight: 600;
            color: #1a1a1a;
        }

        .module-desc {
            font-size: 9pt;
            color: #6c757d;
            margin-top: 2px;
        }

        /* Compliance Box */
        .compliance-box {
            background: #d1e7dd;
            border: 1px solid #badbcc;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 15px;
        }

        .compliance-title {
            font-weight: bold;
            color: #0f5132;
            margin-bottom: 8px;
        }

        .compliance-list {
            margin: 0;
            padding-left: 20px;
            color: #0f5132;
        }

        .compliance-list li {
            margin-bottom: 4px;
        }

        /* Audit Trail Box */
        .audit-trail-box {
            background: #fff3cd;
            border: 1px solid #ffecb5;
            border-radius: 6px;
            padding: 15px;
        }

        .audit-trail-box ul {
            margin: 0;
            padding-left: 20px;
            color: #664d03;
        }

        .audit-trail-box li {
            margin-bottom: 5px;
        }

        /* Statement Box */
        .statement-box {
            background: #e7f1ff;
            border-left: 4px solid #0d6efd;
            padding: 15px 20px;
            font-style: italic;
            color: #495057;
            margin-bottom: 20px;
        }

        /* Signature Section */
        .signature-section {
            margin-top: 30px;
            page-break-inside: avoid;
        }

        .signature-box {
            border: 1px solid #dee2e6;
            padding: 20px;
            text-align: center;
            max-width: 300px;
            margin: 0 auto;
        }

        .signature-title {
            font-weight: bold;
            color: #495057;
            margin-bottom: 40px;
        }

        .signature-line {
            border-top: 1px solid #1a1a1a;
            margin: 0 30px;
            padding-top: 10px;
        }

        .signature-name {
            font-weight: bold;
            color: #1a1a1a;
        }

        .signature-position {
            font-size: 9pt;
            color: #6c757d;
        }

        /* Integrity Section */
        .integrity-box {
            background: #f8f9fa;
            border: 2px dashed #6c757d;
            border-radius: 6px;
            padding: 15px;
            margin-top: 20px;
        }

        .integrity-title {
            font-weight: bold;
            color: #495057;
            margin-bottom: 10px;
        }

        .hash-value {
            font-family: 'DejaVu Sans Mono', monospace;
            font-size: 7pt;
            background: #e9ecef;
            padding: 8px;
            border-radius: 4px;
            word-break: break-all;
            color: #495057;
        }

        .integrity-info {
            margin-top: 10px;
            font-size: 8pt;
            color: #6c757d;
        }

        /* Footer */
        .page-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            font-size: 7pt;
            color: #6c757d;
            border-top: 1px solid #dee2e6;
            padding-top: 8px;
        }

        .footer-left {
            float: left;
        }

        .footer-right {
            float: right;
        }

        /* Utility Classes */
        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-muted {
            color: #6c757d;
        }

        .text-primary {
            color: #0d6efd;
        }

        .text-success {
            color: #198754;
        }

        .text-danger {
            color: #dc3545;
        }

        .mb-0 {
            margin-bottom: 0;
        }

        .mt-0 {
            margin-top: 0;
        }

        .small {
            font-size: 8pt;
        }

        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 8pt;
            font-weight: bold;
        }

        .badge-primary {
            background: #0d6efd;
            color: #fff;
        }

        .badge-success {
            background: #198754;
            color: #fff;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <!-- Watermark -->
    <div class="watermark">OFFICIAL AUDIT DOCUMENT – LSP</div>

    <!-- =====================================================
         COVER PAGE
         ===================================================== -->
    @php
        $logoBase64 = systemLogoBase64();
        $logoInfo = systemLogoInfo();
    @endphp
    <div class="cover-page">
        <div class="cover-header">
            @if($logoBase64)
                <img src="{{ $logoBase64 }}" alt="Logo" style="max-width: 80px; max-height: 60px; margin-bottom: 10px;">
            @elseif($logoInfo['nama_perusahaan'])
                <div class="cover-logo">{{ $logoInfo['nama_perusahaan'] }}</div>
            @else
                <div class="cover-logo">LSP</div>
            @endif
            <div class="cover-lsp-name">{{ $logoInfo['nama_perusahaan'] ?? $lsp['nama'] ?? 'Lembaga Sertifikasi Profesi' }}</div>
        </div>

        <div class="cover-main">
            <div class="cover-title">PAKET AUDIT<br>SISTEM INFORMASI SERTIFIKASI</div>
            <div class="cover-subtitle">Lembaga Sertifikasi Profesi</div>

            <div class="cover-info-box">
                <div class="cover-info-row">
                    <div class="cover-info-label">Nama LSP</div>
                    <div class="cover-info-value">{{ $logoInfo['nama_perusahaan'] ?? $lsp['nama'] ?? '-' }}</div>
                </div>
                <div class="cover-info-row">
                    <div class="cover-info-label">Nomor Lisensi BNSP</div>
                    <div class="cover-info-value">{{ $lsp['nomor_lisensi'] ?? '-' }}</div>
                </div>
                <div class="cover-info-row">
                    <div class="cover-info-label">Alamat</div>
                    <div class="cover-info-value">{{ $lsp['alamat'] ?? '-' }}</div>
                </div>
                <div class="cover-info-row">
                    <div class="cover-info-label">Nama Sistem</div>
                    <div class="cover-info-value"><strong>{{ $system['nama'] ?? ($logoInfo['nama_perusahaan'] ?? 'Sistem Sertifikasi') }}</strong></div>
                </div>
                <div class="cover-info-row">
                    <div class="cover-info-label">Versi Sistem</div>
                    <div class="cover-info-value">{{ $system['versi'] ?? '1.0.0' }}</div>
                </div>
                <div class="cover-info-row">
                    <div class="cover-info-label">Tanggal Audit</div>
                    <div class="cover-info-value">{{ $document['audit_date'] ?? now()->format('d F Y') }}</div>
                </div>
                <div class="cover-info-row">
                    <div class="cover-info-label">Periode Audit</div>
                    <div class="cover-info-value">{{ $document['audit_period'] ?? '-' }}</div>
                </div>
            </div>
        </div>

        <div class="cover-footer">
            <div class="cover-confidential">CONFIDENTIAL – FOR AUDIT PURPOSES ONLY</div>
            <div class="cover-compliance">
                Dokumen ini dibuat sesuai dengan standar BNSP dan ISO/IEC 17024:2012
            </div>
        </div>
    </div>

    <!-- =====================================================
         SECTION 1: INFORMASI UMUM SISTEM
         ===================================================== -->
    <div class="page">
        <div class="page-header">
            <div class="page-header-left">
                <div class="page-header-logo">{{ $logoInfo['nama_perusahaan'] ?? 'LSP' }}</div>
                <div class="page-header-title">Paket Audit Sistem Informasi Sertifikasi</div>
            </div>
            <div class="page-header-right">
                Dokumen: {{ $document['document_number'] ?? '-' }}<br>
                Tanggal: {{ $document['audit_date'] ?? '-' }}
            </div>
        </div>

        <div class="section">
            <div class="section-title">
                <span class="section-number">1</span>Informasi Umum Sistem
            </div>

            <div class="content-box">
                <div class="subsection-title">Deskripsi Sistem</div>
                <p>{{ $system['deskripsi'] ?? '-' }}</p>
            </div>

            <div class="content-box">
                <div class="subsection-title">Ruang Lingkup Penggunaan</div>
                <p>{{ $system['ruang_lingkup'] ?? '-' }}</p>
            </div>

            <div class="subsection-title">Statistik Sistem</div>
            <div class="stats-grid">
                <div class="stats-item">
                    <div class="stats-number">{{ $statistics['total_skema'] ?? 0 }}</div>
                    <div class="stats-label">Skema Aktif</div>
                </div>
                <div class="stats-item">
                    <div class="stats-number">{{ $statistics['total_pendaftaran'] ?? 0 }}</div>
                    <div class="stats-label">Pendaftaran</div>
                </div>
                <div class="stats-item">
                    <div class="stats-number">{{ $statistics['total_sertifikat'] ?? 0 }}</div>
                    <div class="stats-label">Sertifikat Aktif</div>
                </div>
                <div class="stats-item">
                    <div class="stats-number">{{ $statistics['total_asesor'] ?? 0 }}</div>
                    <div class="stats-label">Asesor</div>
                </div>
            </div>

            <div class="subsection-title">Modul Utama Sistem</div>
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th style="width: 5%;">No</th>
                        <th style="width: 30%;">Nama Modul</th>
                        <th>Deskripsi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($modules as $index => $module)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td><strong>{{ $module['nama'] }}</strong></td>
                        <td>{{ $module['deskripsi'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- =====================================================
         SECTION 2: KEPATUHAN REGULASI
         ===================================================== -->
    <div class="page">
        <div class="page-header">
            <div class="page-header-left">
                <div class="page-header-logo">{{ $logoInfo['nama_perusahaan'] ?? 'LSP' }}</div>
                <div class="page-header-title">Paket Audit Sistem Informasi Sertifikasi</div>
            </div>
            <div class="page-header-right">
                Dokumen: {{ $document['document_number'] ?? '-' }}<br>
                Tanggal: {{ $document['audit_date'] ?? '-' }}
            </div>
        </div>

        <div class="section">
            <div class="section-title">
                <span class="section-number">2</span>Kepatuhan Regulasi
            </div>

            <p style="margin-bottom: 15px;">
                Sistem {{ $system['nama'] ?? ($logoInfo['nama_perusahaan'] ?? 'ini') }} dirancang dan dioperasikan sesuai dengan 
                regulasi dan standar berikut:
            </p>

            @foreach($compliance as $key => $item)
            <div class="compliance-box">
                <div class="compliance-title">{{ $item['title'] }}</div>
                <ul class="compliance-list">
                    @foreach($item['items'] as $point)
                    <li>{{ $point }}</li>
                    @endforeach
                </ul>
            </div>
            @endforeach

            <div class="content-box highlight">
                <div class="subsection-title mt-0">Catatan Penting</div>
                <ul style="margin: 0; padding-left: 20px;">
                    <li>Sistem mendukung pemisahan peran (role-based access control)</li>
                    <li>Semua aktivitas tercatat dalam audit trail</li>
                    <li>Data terenkripsi dan dilindungi sesuai standar keamanan</li>
                    <li>Backup data dilakukan secara berkala</li>
                </ul>
            </div>
        </div>

        <!-- =====================================================
             SECTION 3: DAFTAR AUDIT EVIDENCE (INDEX)
             ===================================================== -->
        <div class="section">
            <div class="section-title">
                <span class="section-number">3</span>Daftar Audit Evidence (Index)
            </div>

            <p style="margin-bottom: 15px;">
                Berikut adalah daftar bukti audit yang dapat digenerate oleh sistem:
            </p>

            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 5%;">No</th>
                        <th style="width: 30%;">Nama Dokumen</th>
                        <th style="width: 15%;">Modul</th>
                        <th style="width: 10%;">Jenis</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($auditEvidence as $index => $evidence)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td><strong>{{ $evidence['nama'] }}</strong></td>
                        <td>{{ $evidence['modul'] }}</td>
                        <td class="text-center"><span class="badge badge-primary">{{ $evidence['jenis'] }}</span></td>
                        <td>{{ $evidence['keterangan'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="small text-muted">
                * Setiap dokumen audit evidence dapat diakses melalui menu AdminUI → Audit Evidence
            </div>
        </div>
    </div>

    <!-- =====================================================
         SECTION 4 & 5: AUDIT TRAIL & PERNYATAAN INTEGRITAS
         ===================================================== -->
    <div class="page">
        <div class="page-header">
            <div class="page-header-left">
                <div class="page-header-logo">{{ $logoInfo['nama_perusahaan'] ?? 'LSP' }}</div>
                <div class="page-header-title">Paket Audit Sistem Informasi Sertifikasi</div>
            </div>
            <div class="page-header-right">
                Dokumen: {{ $document['document_number'] ?? '-' }}<br>
                Tanggal: {{ $document['audit_date'] ?? '-' }}
            </div>
        </div>

        <div class="section">
            <div class="section-title">
                <span class="section-number">4</span>Mekanisme Audit Trail
            </div>

            <p style="margin-bottom: 15px;">
                Sistem {{ $system['nama'] ?? ($logoInfo['nama_perusahaan'] ?? 'ini') }} dilengkapi dengan mekanisme audit trail 
                yang mencatat seluruh aktivitas secara otomatis:
            </p>

            <div class="audit-trail-box">
                <ul>
                    <li><strong>Pencatatan Otomatis:</strong> Semua aktivitas CRUD tercatat tanpa intervensi manual</li>
                    <li><strong>Immutable Log:</strong> Data audit tidak dapat diubah atau dihapus</li>
                    <li><strong>Timestamp & IP Address:</strong> Setiap log mencatat waktu dan alamat IP pengguna</li>
                    <li><strong>User Tracking:</strong> Identitas pengguna yang melakukan aktivitas tercatat</li>
                    <li><strong>UUID & SHA-256 Hash:</strong> Setiap dokumen memiliki identitas unik untuk verifikasi</li>
                    <li><strong>Event Classification:</strong> Aktivitas dikategorikan berdasarkan jenis event</li>
                </ul>
            </div>

            <div class="content-box" style="margin-top: 15px;">
                <div class="info-grid">
                    <div class="info-row">
                        <div class="info-label">Total Audit Log</div>
                        <div class="info-value"><strong>{{ number_format($statistics['total_audit_logs'] ?? 0) }}</strong> records</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">
                <span class="section-number">5</span>Pernyataan Integritas Sistem
            </div>

            <div class="statement-box">
                Dokumen ini dihasilkan secara otomatis oleh sistem {{ $system['nama'] ?? ($logoInfo['nama_perusahaan'] ?? 'sertifikasi') }}.
                Seluruh data dan bukti yang tercantum dapat diverifikasi dan ditelusuri melalui 
                sistem informasi LSP. Integritas dokumen dijamin melalui mekanisme hash kriptografi
                dan UUID yang tercantum pada setiap halaman audit evidence.
            </div>

            <div class="content-box">
                <ul style="margin: 0; padding-left: 20px;">
                    <li>Dokumen ini valid tanpa tanda tangan basah</li>
                    <li>Verifikasi dapat dilakukan melalui hash SHA-256</li>
                    <li>Setiap modifikasi akan menghasilkan hash berbeda</li>
                    <li>Dokumen asli tersimpan dalam sistem database</li>
                </ul>
            </div>
        </div>

        <!-- =====================================================
             SECTION 6: PENANGGUNG JAWAB
             ===================================================== -->
        <div class="section">
            <div class="section-title">
                <span class="section-number">6</span>Penanggung Jawab
            </div>

            <div class="signature-section">
                <div class="signature-box">
                    <div class="signature-title">Ketua LSP</div>
                    <div class="signature-line">
                        <div class="signature-name">{{ $lsp['ketua_lsp'] ?? '-' }}</div>
                        <div class="signature-position">{{ $lsp['jabatan_ketua'] ?? '-' }}</div>
                    </div>
                </div>
            </div>

            <div class="content-box" style="margin-top: 20px;">
                <div class="info-grid">
                    <div class="info-row">
                        <div class="info-label">Tanggal Penetapan</div>
                        <div class="info-value">{{ $document['audit_date'] ?? now()->format('d F Y') }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Periode Audit</div>
                        <div class="info-value">{{ $document['audit_period'] ?? '-' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Integrity Box -->
        <div class="integrity-box">
            <div class="integrity-title">🔒 Integritas Dokumen</div>
            <div class="hash-value">
                <strong>SHA-256:</strong> {{ $document['hash'] ?? '-' }}
            </div>
            <div class="integrity-info">
                <div><strong>UUID:</strong> {{ $document['uuid'] ?? '-' }}</div>
                <div><strong>Nomor Dokumen:</strong> {{ $document['document_number'] ?? '-' }}</div>
                <div><strong>Digenerate:</strong> {{ $document['generated_at'] ?? '-' }} oleh {{ $document['generated_by'] ?? 'System' }} ({{ $document['generated_by_role'] ?? '-' }})</div>
            </div>
            <div style="margin-top: 10px; font-size: 7pt; color: #6c757d; font-style: italic;">
                * Hash ini digunakan untuk memverifikasi integritas dokumen. Modifikasi apapun akan menghasilkan hash berbeda.
            </div>
        </div>
    </div>

    <!-- Footer (fixed) -->
    <div class="page-footer">
        <div class="footer-left">
            {{ $lsp['nama'] ?? ($logoInfo['nama_perusahaan'] ?? 'LSP') }} – Sistem {{ $system['nama'] ?? 'Sertifikasi' }} v{{ $system['versi'] ?? '1.0.0' }}
        </div>
        <div class="footer-right">
            CONFIDENTIAL – Dokumen ini valid tanpa tanda tangan basah
        </div>
    </div>
</body>
</html>
