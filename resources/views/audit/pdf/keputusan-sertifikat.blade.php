<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Audit Evidence Keputusan & Sertifikat - {{ $pendaftaran->nomor_pendaftaran }}</title>
    <style>
        /* Reset & Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10pt;
            line-height: 1.4;
            color: #333;
            position: relative;
        }

        /* Watermark */
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 72pt;
            color: rgba(200, 200, 200, 0.15);
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 10px;
            z-index: -1;
            white-space: nowrap;
        }

        /* Page Structure */
        .page {
            padding: 15mm 12mm;
            min-height: 100%;
        }

        /* Header Styles */
        .header {
            border-bottom: 3px solid #1a365d;
            padding-bottom: 12px;
            margin-bottom: 15px;
        }

        .header-content {
            display: table;
            width: 100%;
        }

        .logo-section {
            display: table-cell;
            width: 70px;
            vertical-align: middle;
        }

        .logo-placeholder {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #1a365d 0%, #2d3748 100%);
            border-radius: 8px;
            text-align: center;
            line-height: 60px;
            color: white;
            font-weight: bold;
            font-size: 12pt;
        }

        .title-section {
            display: table-cell;
            vertical-align: middle;
            padding-left: 12px;
        }

        .main-title {
            font-size: 13pt;
            font-weight: bold;
            color: #1a365d;
            margin-bottom: 4px;
        }

        .sub-title {
            font-size: 10pt;
            color: #4a5568;
        }

        .document-info {
            display: table-cell;
            width: 180px;
            vertical-align: middle;
            text-align: right;
        }

        .doc-number {
            font-size: 8pt;
            color: #718096;
            margin-bottom: 2px;
        }

        /* Section Styles */
        .section {
            margin-bottom: 15px;
            page-break-inside: avoid;
        }

        .section-title {
            background: linear-gradient(135deg, #1a365d 0%, #2d3748 100%);
            color: white;
            padding: 6px 10px;
            font-size: 10pt;
            font-weight: bold;
            margin-bottom: 8px;
            border-radius: 4px;
        }

        .section-number {
            background: rgba(255,255,255,0.2);
            padding: 2px 6px;
            border-radius: 3px;
            margin-right: 6px;
        }

        /* Table Styles */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        .data-table th,
        .data-table td {
            padding: 6px 8px;
            text-align: left;
            border: 1px solid #e2e8f0;
            font-size: 9pt;
        }

        .data-table th {
            background: #f7fafc;
            font-weight: 600;
            color: #2d3748;
            width: 30%;
        }

        .data-table td {
            color: #4a5568;
        }

        .data-table tr:nth-child(even) td {
            background: #fafafa;
        }

        /* Summary Box */
        .summary-box {
            background: #f7fafc;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px;
        }

        .summary-grid {
            display: table;
            width: 100%;
        }

        .summary-item {
            display: table-cell;
            text-align: center;
            padding: 6px;
            width: 25%;
        }

        .summary-value {
            font-size: 16pt;
            font-weight: bold;
            color: #2d3748;
        }

        .summary-value.success {
            color: #38a169;
        }

        .summary-value.danger {
            color: #e53e3e;
        }

        .summary-label {
            font-size: 8pt;
            color: #718096;
            margin-top: 2px;
        }

        .conclusion-box {
            margin-top: 10px;
            padding: 8px 12px;
            border-radius: 6px;
            text-align: center;
        }

        .conclusion-box.layak {
            background: #c6f6d5;
            border: 2px solid #48bb78;
        }

        .conclusion-box.tidak-layak {
            background: #fed7d7;
            border: 2px solid #f56565;
        }

        .conclusion-text {
            font-size: 11pt;
            font-weight: bold;
        }

        .conclusion-box.layak .conclusion-text {
            color: #22543d;
        }

        .conclusion-box.tidak-layak .conclusion-text {
            color: #742a2a;
        }

        /* Decision Card */
        .decision-card {
            border: 3px solid;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
        }

        .decision-card.kompeten {
            border-color: #48bb78;
            background: linear-gradient(135deg, #f0fff4 0%, #c6f6d5 100%);
        }

        .decision-card.belum-kompeten {
            border-color: #f56565;
            background: linear-gradient(135deg, #fff5f5 0%, #fed7d7 100%);
        }

        .decision-label {
            font-size: 9pt;
            color: #718096;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }

        .decision-value {
            font-size: 20pt;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .decision-card.kompeten .decision-value {
            color: #22543d;
        }

        .decision-card.belum-kompeten .decision-value {
            color: #742a2a;
        }

        .decision-locked {
            display: inline-block;
            background: #2d3748;
            color: white;
            padding: 3px 10px;
            border-radius: 4px;
            font-size: 8pt;
            font-weight: bold;
        }

        /* Komite Teknis Card */
        .komite-card {
            background: linear-gradient(135deg, #5a67d8 0%, #667eea 100%);
            color: white;
            padding: 12px;
            border-radius: 6px;
            margin-top: 10px;
        }

        .komite-label {
            font-size: 8pt;
            opacity: 0.8;
            margin-bottom: 2px;
        }

        .komite-name {
            font-size: 11pt;
            font-weight: bold;
            margin-bottom: 4px;
        }

        .komite-detail {
            font-size: 8pt;
            opacity: 0.9;
        }

        /* Certificate Section */
        .certificate-box {
            border: 3px solid #d69e2e;
            border-radius: 10px;
            background: linear-gradient(135deg, #fffff0 0%, #fefcbf 100%);
            padding: 15px;
        }

        .certificate-header {
            text-align: center;
            margin-bottom: 10px;
        }

        .certificate-icon {
            font-size: 24pt;
            color: #d69e2e;
        }

        .certificate-title {
            font-size: 12pt;
            font-weight: bold;
            color: #744210;
            margin-top: 5px;
        }

        .certificate-grid {
            display: table;
            width: 100%;
        }

        .certificate-info {
            display: table-cell;
            width: 65%;
            vertical-align: top;
        }

        .certificate-qr {
            display: table-cell;
            width: 35%;
            text-align: center;
            vertical-align: middle;
        }

        .qr-label {
            font-size: 8pt;
            color: #718096;
            margin-top: 5px;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 9pt;
            font-weight: bold;
        }

        .status-aktif {
            background: #c6f6d5;
            color: #22543d;
        }

        .status-kadaluarsa {
            background: #fed7d7;
            color: #742a2a;
        }

        /* Audit Log Table */
        .audit-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
        }

        .audit-table th {
            background: #2d3748;
            color: white;
            padding: 6px;
            text-align: left;
            font-weight: 600;
        }

        .audit-table td {
            padding: 5px 6px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: top;
        }

        .audit-table tr:nth-child(even) td {
            background: #f7fafc;
        }

        .action-badge {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 7pt;
            font-weight: 600;
            text-transform: uppercase;
        }

        .action-create { background: #c6f6d5; color: #22543d; }
        .action-update { background: #bee3f8; color: #2a4365; }
        .action-view { background: #e9d8fd; color: #44337a; }
        .action-decide { background: #fefcbf; color: #744210; }
        .action-issue { background: #c6f6d5; color: #22543d; }
        .action-approve { background: #c6f6d5; color: #22543d; }

        /* Integrity Section */
        .integrity-box {
            background: #f7fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 12px;
        }

        .integrity-item {
            margin-bottom: 8px;
        }

        .integrity-item:last-child {
            margin-bottom: 0;
        }

        .integrity-label {
            font-size: 8pt;
            color: #718096;
            margin-bottom: 2px;
        }

        .integrity-value {
            font-family: 'DejaVu Sans Mono', monospace;
            font-size: 8pt;
            color: #2d3748;
            background: white;
            padding: 4px 6px;
            border-radius: 3px;
            border: 1px solid #e2e8f0;
            word-break: break-all;
        }

        .hash-value {
            font-size: 7pt;
            letter-spacing: 0.3px;
        }

        /* Footer Styles */
        .footer {
            position: fixed;
            bottom: 12mm;
            left: 12mm;
            right: 12mm;
            border-top: 2px solid #1a365d;
            padding-top: 8px;
        }

        .footer-content {
            display: table;
            width: 100%;
        }

        .footer-left {
            display: table-cell;
            width: 70%;
            vertical-align: top;
        }

        .footer-right {
            display: table-cell;
            width: 30%;
            vertical-align: top;
            text-align: right;
        }

        .compliance-text {
            font-size: 7pt;
            color: #718096;
            line-height: 1.4;
        }

        .compliance-badge {
            display: inline-block;
            background: #1a365d;
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 7pt;
            font-weight: bold;
            margin-bottom: 4px;
        }

        .page-number {
            font-size: 8pt;
            color: #718096;
        }

        /* Page break control */
        .page-break {
            page-break-before: always;
        }

        /* Notice Box */
        .notice-box {
            background: #fefcbf;
            border-left: 4px solid #d69e2e;
            padding: 8px 12px;
            margin: 8px 0;
            font-size: 8pt;
            color: #744210;
        }

        /* Chain Audit Info */
        .chain-info {
            background: #ebf4ff;
            border: 1px solid #bee3f8;
            border-radius: 6px;
            padding: 10px;
            margin-bottom: 15px;
            font-size: 8pt;
            color: #2a4365;
        }

        .chain-step {
            display: inline-block;
            padding: 3px 8px;
            background: #4299e1;
            color: white;
            border-radius: 3px;
            font-weight: bold;
            margin-right: 5px;
        }

        .chain-arrow {
            color: #4299e1;
            margin: 0 3px;
        }
    </style>
</head>
<body>
    <!-- Watermark -->
    <div class="watermark">CONFIDENTIAL – LSP</div>

    <div class="page">
        <!-- Header -->
        @php
            $logoBase64 = systemLogoBase64();
            $logoInfo = systemLogoInfo();
        @endphp
        <div class="header">
            <div class="header-content">
                <div class="logo-section">
                    @if($logoBase64)
                        <img src="{{ $logoBase64 }}" alt="Logo LSP" style="max-width: 60px; max-height: 60px; height: auto;">
                    @else
                        <div class="logo-placeholder">LSP</div>
                    @endif
                </div>
                <div class="title-section">
                    <div class="main-title">AUDIT EVIDENCE – KEPUTUSAN & SERTIFIKAT</div>
                    @if($logoInfo['nama_perusahaan'])
                        <div class="sub-title">{{ $logoInfo['nama_perusahaan'] }}</div>
                    @else
                        <div class="sub-title">Lembaga Sertifikasi Profesi</div>
                    @endif
                </div>
                <div class="document-info">
                    <div class="doc-number"><strong>No. Dokumen:</strong> {{ $documentNumber }}</div>
                    <div class="doc-number"><strong>No. Pendaftaran:</strong> {{ $pendaftaran->nomor_pendaftaran }}</div>
                    @if($sertifikat)
                    <div class="doc-number"><strong>No. Sertifikat:</strong> {{ $sertifikat->nomor_sertifikat }}</div>
                    @endif
                    <div class="doc-number"><strong>Tanggal Cetak:</strong> {{ $generatedAt->translatedFormat('d F Y, H:i') }} WIB</div>
                </div>
            </div>
        </div>

        <!-- Chain Audit Info -->
        <div class="chain-info">
            <strong>Rantai Audit Sertifikasi:</strong><br>
            <span class="chain-step">1</span> Pendaftaran
            <span class="chain-arrow">→</span>
            <span class="chain-step">2</span> Asesmen
            <span class="chain-arrow">→</span>
            <span class="chain-step">3</span> Keputusan
            @if($sertifikat)
            <span class="chain-arrow">→</span>
            <span class="chain-step">4</span> Sertifikat ✓
            @endif
            <span style="float: right; color: #38a169; font-weight: bold;">
                @if($sertifikat)
                    RANTAI LENGKAP
                @else
                    MENUNGGU SERTIFIKAT
                @endif
            </span>
        </div>

        <!-- Section 1: Identitas Peserta -->
        <div class="section">
            <div class="section-title">
                <span class="section-number">1</span>
                Identitas Peserta / Asesi
            </div>
            <table class="data-table">
                <tr>
                    <th>Nama Lengkap</th>
                    <td><strong>{{ $pendaftaran->asesi_name }}</strong></td>
                </tr>
                <tr>
                    <th>Nomor Pendaftaran</th>
                    <td>{{ $pendaftaran->nomor_pendaftaran }}</td>
                </tr>
                <tr>
                    <th>Skema Sertifikasi</th>
                    <td>
                        @if($pendaftaran->skemaSertifikasi)
                            <strong>{{ $pendaftaran->skemaSertifikasi->kode_skema }}</strong> - 
                            {{ $pendaftaran->skemaSertifikasi->nama_skema }}
                        @else
                            -
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>Tanggal Asesmen</th>
                    <td>{{ $asesmen?->tanggal_asesmen?->translatedFormat('d F Y') ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Status Akhir Peserta</th>
                    <td>
                        <span class="status-badge {{ $keputusan->isKompeten() ? 'status-aktif' : 'status-kadaluarsa' }}">
                            {{ $keputusan->keputusan_label }}
                        </span>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Section 2: Ringkasan Hasil Asesmen -->
        <div class="section">
            <div class="section-title">
                <span class="section-number">2</span>
                Ringkasan Hasil Asesmen
            </div>
            @if($asesmenSummary)
            <div class="summary-box">
                <div class="summary-grid">
                    <div class="summary-item">
                        <div class="summary-value">{{ $asesmenSummary['total_unit'] }}</div>
                        <div class="summary-label">Total Unit Kompetensi</div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-value">{{ $asesmenSummary['total_kuk'] }}</div>
                        <div class="summary-label">Total KUK</div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-value success">{{ $asesmenSummary['total_kompeten'] }}</div>
                        <div class="summary-label">Jumlah Kompeten</div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-value danger">{{ $asesmenSummary['total_belum_kompeten'] }}</div>
                        <div class="summary-label">Jumlah Belum Kompeten</div>
                    </div>
                </div>

                <div class="conclusion-box {{ $asesmenSummary['kesimpulan'] === 'LAYAK DITETAPKAN' ? 'layak' : 'tidak-layak' }}">
                    <div class="conclusion-text">
                        Kesimpulan Asesmen: {{ $asesmenSummary['kesimpulan'] }}
                    </div>
                </div>
            </div>
            <div style="font-size: 8pt; color: #718096; margin-top: 5px; font-style: italic;">
                * Ringkasan diambil dari Audit Evidence Asesmen. Persentase Kompeten: {{ $asesmenSummary['persentase_kompeten'] }}%
            </div>
            @else
            <div class="notice-box">
                <strong>Perhatian:</strong> Data asesmen tidak tersedia.
            </div>
            @endif
        </div>

        <!-- Section 3: Keputusan Komite Teknis -->
        <div class="section">
            <div class="section-title">
                <span class="section-number">3</span>
                Keputusan Komite Teknis
            </div>
            
            <div class="decision-card {{ $keputusan->isKompeten() ? 'kompeten' : 'belum-kompeten' }}">
                <div class="decision-label">Keputusan Akhir</div>
                <div class="decision-value">{{ strtoupper($keputusan->keputusan_label) }}</div>
                @if($keputusan->isLocked())
                <div class="decision-locked">
                    <i class="fas fa-lock"></i> FINAL & LOCKED
                </div>
                @endif
            </div>

            <table class="data-table" style="margin-top: 10px;">
                <tr>
                    <th>Dasar Keputusan</th>
                    <td>
                        @if($keputusan->isKompeten())
                            Asesi telah memenuhi seluruh kriteria unjuk kerja pada setiap unit kompetensi sesuai standar SKKNI dan persyaratan skema sertifikasi.
                        @else
                            Asesi belum memenuhi seluruh kriteria unjuk kerja yang dipersyaratkan pada skema sertifikasi.
                        @endif
                    </td>
                </tr>
                @if($keputusan->catatan_komite)
                <tr>
                    <th>Catatan Komite Teknis</th>
                    <td>{{ $keputusan->catatan_komite }}</td>
                </tr>
                @endif
                <tr>
                    <th>Tanggal Keputusan</th>
                    <td>{{ $keputusan->tanggal_keputusan?->translatedFormat('d F Y') ?? '-' }}</td>
                </tr>
            </table>

            <div class="komite-card">
                <div class="komite-label">Nama Penetap</div>
                <div class="komite-name">{{ $keputusan->penetap?->name ?? 'N/A' }}</div>
                <div class="komite-detail">
                    <strong>Role:</strong> KOMITE TEKNIS &nbsp;&nbsp;|&nbsp;&nbsp;
                    <strong>Waktu Penetapan:</strong> {{ $keputusan->created_at->translatedFormat('d F Y, H:i') }} WIB
                </div>
            </div>

            <div style="font-size: 8pt; color: #e53e3e; margin-top: 8px; font-weight: bold;">
                ⚠️ Keputusan bersifat FINAL & LOCKED. Asesor TIDAK tercatat sebagai penetap keputusan (Separation of Duties).
            </div>
        </div>

        <!-- Page break before sertifikat section -->
        <div class="page-break"></div>

        <!-- Section 4: Informasi Sertifikat (only if kompeten) -->
        @if($keputusan->isKompeten())
        <div class="section">
            <div class="section-title">
                <span class="section-number">4</span>
                Informasi Sertifikat Kompetensi
            </div>
            
            @if($sertifikat)
            <div class="certificate-box">
                <div class="certificate-header">
                    <div class="certificate-icon">🏆</div>
                    <div class="certificate-title">SERTIFIKAT KOMPETENSI DITERBITKAN</div>
                </div>

                <div class="certificate-grid">
                    <div class="certificate-info">
                        <table class="data-table">
                            <tr>
                                <th>Nomor Sertifikat</th>
                                <td><strong>{{ $sertifikat->nomor_sertifikat }}</strong></td>
                            </tr>
                            <tr>
                                <th>UUID Sertifikat</th>
                                <td style="font-family: monospace; font-size: 8pt;">{{ $sertifikat->uuid }}</td>
                            </tr>
                            <tr>
                                <th>Tanggal Terbit</th>
                                <td>{{ $sertifikat->tanggal_terbit?->translatedFormat('d F Y') ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Masa Berlaku</th>
                                <td>{{ $sertifikat->tanggal_berlaku_sampai?->translatedFormat('d F Y') ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Status Sertifikat</th>
                                <td>
                                    <span class="status-badge {{ $sertifikat->isValid() ? 'status-aktif' : 'status-kadaluarsa' }}">
                                        {{ $sertifikat->isValid() ? 'AKTIF' : 'KADALUARSA' }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Diterbitkan Oleh</th>
                                <td>{{ $sertifikat->penerbit?->name ?? 'System' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="certificate-qr">
                        @if($qrCodeBase64)
                        <div style="background: white; padding: 10px; border-radius: 8px; display: inline-block;">
                            <img src="data:image/svg+xml;base64,{{ $qrCodeBase64 }}" width="100" height="100" alt="QR Code">
                        </div>
                        <div class="qr-label">Scan untuk Verifikasi Publik</div>
                        @else
                        <div style="background: #f7fafc; padding: 20px; border-radius: 8px; color: #718096; font-size: 8pt;">
                            QR Code tidak tersedia
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @else
            <div class="notice-box">
                <strong>Menunggu Penerbitan:</strong> Keputusan telah ditetapkan KOMPETEN. 
                Sertifikat belum diterbitkan oleh sistem. Silakan proses penerbitan sertifikat.
            </div>
            @endif
        </div>
        @else
        <div class="section">
            <div class="section-title">
                <span class="section-number">4</span>
                Informasi Sertifikat Kompetensi
            </div>
            <div class="notice-box" style="background: #fed7d7; border-color: #e53e3e; color: #742a2a;">
                <strong>Tidak Diterbitkan:</strong> Sertifikat tidak diterbitkan karena keputusan akhir adalah BELUM KOMPETEN.
            </div>
        </div>
        @endif

        <!-- Section 5: Audit Log Ringkas -->
        <div class="section">
            <div class="section-title">
                <span class="section-number">5</span>
                Riwayat Audit Log
            </div>
            @if($auditLogs->count() > 0)
            <table class="audit-table">
                <thead>
                    <tr>
                        <th style="width: 110px;">Waktu (WIB)</th>
                        <th style="width: 70px;">Aksi</th>
                        <th style="width: 90px;">Pengguna</th>
                        <th>Event / Deskripsi</th>
                        <th style="width: 90px;">IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($auditLogs as $log)
                    <tr>
                        <td>{{ $log->created_at->translatedFormat('d/m/Y H:i') }}</td>
                        <td>
                            <span class="action-badge action-{{ $log->action }}">
                                {{ $log->action_label }}
                            </span>
                        </td>
                        <td>
                            {{ $log->user_name }}<br>
                            <span style="font-size: 7pt; color: #718096;">({{ $log->user_role }})</span>
                        </td>
                        <td>{{ Str::limit($log->description, 80) }}</td>
                        <td style="font-size: 7pt;">{{ $log->ip_address ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div style="font-size: 7pt; color: #718096; margin-top: 4px; text-align: right;">
                Menampilkan {{ $auditLogs->count() }} log terakhir | Data bersifat immutable
            </div>
            @else
            <div class="notice-box">
                Belum ada riwayat audit log.
            </div>
            @endif
        </div>

        <!-- Section 6: Integritas Data -->
        <div class="section">
            <div class="section-title">
                <span class="section-number">6</span>
                Integritas Data
            </div>
            <div class="integrity-box">
                <div class="integrity-item">
                    <div class="integrity-label">Document UUID</div>
                    <div class="integrity-value">{{ $documentUuid }}</div>
                </div>
                @if($sertifikat)
                <div class="integrity-item">
                    <div class="integrity-label">Sertifikat UUID</div>
                    <div class="integrity-value">{{ $sertifikat->uuid }}</div>
                </div>
                @endif
                <div class="integrity-item">
                    <div class="integrity-label">SHA-256 Integrity Hash</div>
                    <div class="integrity-value hash-value">{{ $integrityHash }}</div>
                </div>
                <div class="integrity-item">
                    <div class="integrity-label">Waktu Generasi Dokumen</div>
                    <div class="integrity-value">{{ $generatedAt->toIso8601String() }}</div>
                </div>
                <div class="integrity-item">
                    <div class="integrity-label">Keputusan Dikunci Pada</div>
                    <div class="integrity-value">{{ $keputusan->updated_at->toIso8601String() }}</div>
                </div>
            </div>
            <div style="font-size: 7pt; color: #718096; margin-top: 6px; font-style: italic;">
                * Hash integritas dihitung berdasarkan data keputusan, sertifikat, dan metadata dokumen.
                Dokumen ini bersifat non-editable, generated by system, dan watermark konsisten.
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <div class="footer-content">
            <div class="footer-left">
                <div class="compliance-badge">BNSP & ISO/IEC 17024 COMPLIANT</div>
                <div class="compliance-text">
                    Dokumen ini merupakan bukti audit resmi penetapan keputusan dan penerbitan sertifikat 
                    kompetensi yang dihasilkan oleh sistem LSP sesuai standar BNSP dan ISO/IEC 17024.
                    Dokumen ini menutup rantai audit dari pendaftaran sampai sertifikat.
                </div>
            </div>
            <div class="footer-right">
                <div class="page-number">
                    Halaman 1-2
                </div>
                <div style="font-size: 7pt; color: #a0aec0; margin-top: 4px;">
                    {{ $documentNumber }}
                </div>
                <div style="font-size: 7pt; color: #a0aec0; margin-top: 2px;">
                    Generated: {{ $generatedAt->format('Y-m-d H:i:s') }}
                </div>
            </div>
        </div>
    </div>
</body>
</html>
