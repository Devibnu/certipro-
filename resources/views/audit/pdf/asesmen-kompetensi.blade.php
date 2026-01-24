<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Audit Evidence Asesmen - {{ $asesmen->pendaftaran?->nomor_pendaftaran ?? 'N/A' }}</title>
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
            border-bottom: 3px solid #2c5282;
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
            background: linear-gradient(135deg, #2c5282 0%, #3182ce 100%);
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
            font-size: 14pt;
            font-weight: bold;
            color: #2c5282;
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

        .doc-date {
            font-size: 8pt;
            color: #718096;
        }

        /* Section Styles */
        .section {
            margin-bottom: 15px;
            page-break-inside: avoid;
        }

        .section-title {
            background: linear-gradient(135deg, #2c5282 0%, #3182ce 100%);
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

        /* Unit Kompetensi Styles */
        .unit-box {
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            margin-bottom: 12px;
            page-break-inside: avoid;
        }

        .unit-header {
            background: #edf2f7;
            padding: 8px 10px;
            border-bottom: 1px solid #e2e8f0;
        }

        .unit-code {
            font-weight: bold;
            color: #2c5282;
            font-size: 9pt;
        }

        .unit-title {
            font-size: 9pt;
            color: #4a5568;
            margin-top: 2px;
        }

        .unit-status {
            float: right;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 8pt;
            font-weight: bold;
        }

        .unit-status.kompeten {
            background: #c6f6d5;
            color: #22543d;
        }

        .unit-status.belum-kompeten {
            background: #fed7d7;
            color: #742a2a;
        }

        .kuk-table {
            width: 100%;
            border-collapse: collapse;
        }

        .kuk-table th {
            background: #2d3748;
            color: white;
            padding: 6px 8px;
            text-align: left;
            font-size: 8pt;
            font-weight: 600;
        }

        .kuk-table td {
            padding: 5px 8px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 8pt;
            vertical-align: top;
        }

        .kuk-table tr:nth-child(even) td {
            background: #f7fafc;
        }

        .kuk-code {
            font-weight: 600;
            color: #2d3748;
            white-space: nowrap;
        }

        .hasil-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 7pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        .hasil-kompeten {
            background: #c6f6d5;
            color: #22543d;
        }

        .hasil-belum-kompeten {
            background: #fed7d7;
            color: #742a2a;
        }

        /* Summary Box */
        .summary-box {
            background: #f7fafc;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
        }

        .summary-grid {
            display: table;
            width: 100%;
        }

        .summary-item {
            display: table-cell;
            text-align: center;
            padding: 8px;
            width: 25%;
        }

        .summary-value {
            font-size: 18pt;
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
            margin-top: 3px;
        }

        .conclusion-box {
            margin-top: 12px;
            padding: 10px 15px;
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
            font-size: 12pt;
            font-weight: bold;
        }

        .conclusion-box.layak .conclusion-text {
            color: #22543d;
        }

        .conclusion-box.tidak-layak .conclusion-text {
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
        .action-assess { background: #fefcbf; color: #744210; }

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
            border-top: 2px solid #2c5282;
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
            background: #2c5282;
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

        /* Asesor Info */
        .asesor-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px;
            border-radius: 6px;
        }

        .asesor-label {
            font-size: 8pt;
            opacity: 0.8;
            margin-bottom: 2px;
        }

        .asesor-name {
            font-size: 11pt;
            font-weight: bold;
            margin-bottom: 4px;
        }

        .asesor-detail {
            font-size: 8pt;
            opacity: 0.9;
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
                    <div class="main-title">AUDIT EVIDENCE – ASESMEN KOMPETENSI</div>
                    @if($logoInfo['nama_perusahaan'])
                        <div class="sub-title">{{ $logoInfo['nama_perusahaan'] }}</div>
                    @else
                        <div class="sub-title">Lembaga Sertifikasi Profesi</div>
                    @endif
                </div>
                <div class="document-info">
                    <div class="doc-number"><strong>No. Dokumen:</strong> {{ $documentNumber }}</div>
                    <div class="doc-number"><strong>No. Pendaftaran:</strong> {{ $asesmen->pendaftaran?->nomor_pendaftaran ?? '-' }}</div>
                    <div class="doc-date"><strong>Tanggal Cetak:</strong> {{ $generatedAt->translatedFormat('d F Y, H:i') }} WIB</div>
                </div>
            </div>
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
                    <td><strong>{{ $asesmen->pendaftaran?->asesi_name ?? '-' }}</strong></td>
                </tr>
                <tr>
                    <th>Nomor Pendaftaran</th>
                    <td>{{ $asesmen->pendaftaran?->nomor_pendaftaran ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Skema Sertifikasi</th>
                    <td>
                        @if($asesmen->pendaftaran?->skemaSertifikasi)
                            <strong>{{ $asesmen->pendaftaran->skemaSertifikasi->kode_skema }}</strong> - 
                            {{ $asesmen->pendaftaran->skemaSertifikasi->nama_skema }}
                        @else
                            -
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>Tanggal Asesmen</th>
                    <td>{{ $asesmen->tanggal_asesmen?->translatedFormat('d F Y') ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Metode Asesmen</th>
                    <td>{{ $asesmen->metode_label }}</td>
                </tr>
            </table>
        </div>

        <!-- Section 2: Identitas Asesor -->
        <div class="section">
            <div class="section-title">
                <span class="section-number">2</span>
                Identitas Asesor
            </div>
            <div class="asesor-card">
                <div class="asesor-label">Nama Asesor</div>
                <div class="asesor-name">{{ $asesmen->asesor?->name ?? 'N/A' }}</div>
                <div class="asesor-detail">
                    <strong>Role:</strong> ASESOR &nbsp;&nbsp;|&nbsp;&nbsp;
                    <strong>Tanggal & Waktu:</strong> {{ $asesmen->tanggal_asesmen?->translatedFormat('d F Y') ?? '-' }}, 
                    {{ $asesmen->created_at->translatedFormat('H:i') }} WIB
                </div>
                @if($asesmen->asesor?->nomor_registrasi)
                <div class="asesor-detail" style="margin-top: 4px;">
                    <strong>No. Registrasi:</strong> {{ $asesmen->asesor->nomor_registrasi }}
                </div>
                @endif
            </div>
        </div>

        <!-- Section 3: Rincian Penilaian -->
        <div class="section">
            <div class="section-title">
                <span class="section-number">3</span>
                Rincian Penilaian per Unit Kompetensi
            </div>
            
            @forelse($detailsByUnit as $unitId => $details)
                @php
                    $unit = $details->first()->unitKompetensi;
                    $hasBelumKompeten = $details->where('hasil', 'belum_kompeten')->count() > 0;
                    $unitStatus = $hasBelumKompeten ? 'belum-kompeten' : 'kompeten';
                    $unitStatusLabel = $hasBelumKompeten ? 'BELUM KOMPETEN' : 'KOMPETEN';
                @endphp
                
                <div class="unit-box">
                    <div class="unit-header">
                        <span class="unit-status {{ $unitStatus }}">{{ $unitStatusLabel }}</span>
                        <div class="unit-code">{{ $unit?->kode_unit ?? 'N/A' }}</div>
                        <div class="unit-title">{{ $unit?->judul_unit ?? 'N/A' }}</div>
                    </div>
                    <table class="kuk-table">
                        <thead>
                            <tr>
                                <th style="width: 80px;">Kode KUK</th>
                                <th>Pernyataan KUK</th>
                                <th style="width: 60px;">Nilai</th>
                                <th style="width: 120px;">Evidence</th>
                                <th style="width: 100px;">Catatan Asesor</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($details as $detail)
                            @php
                                $kukEvidence = isset($evidenceByKuk) ? $evidenceByKuk->get($detail->kuk_id, collect()) : collect();
                            @endphp
                            <tr>
                                <td class="kuk-code">{{ $detail->kuk?->kode_kuk ?? '-' }}</td>
                                <td>{{ $detail->kuk?->pernyataan ?? '-' }}</td>
                                <td>
                                    <span class="hasil-badge hasil-{{ $detail->hasil }}">
                                        {{ $detail->hasil === 'kompeten' ? 'K' : 'BK' }}
                                    </span>
                                </td>
                                <td style="font-size: 7pt;">
                                    @if($kukEvidence->isNotEmpty())
                                        @foreach($kukEvidence as $evidence)
                                            <div style="margin-bottom: 2px;">
                                                @if($evidence->isFile())
                                                    📎 {{ Str::limit($evidence->file_name_original, 20) }}
                                                @else
                                                    🔗 {{ Str::limit($evidence->description ?: parse_url($evidence->link_url, PHP_URL_HOST), 25) }}
                                                @endif
                                            </div>
                                        @endforeach
                                    @else
                                        <span style="color: #999;">-</span>
                                    @endif
                                </td>
                                <td>{{ $detail->catatan ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @empty
                <div class="notice-box">
                    <strong>Perhatian:</strong> Tidak ada data penilaian KUK untuk asesmen ini.
                </div>
            @endforelse
        </div>

        <!-- Section 4: Sampling Audit (Quality Control) -->
        <div class="section">
            <div class="section-title">
                <span class="section-number">4</span>
                Sampling Audit (Pengendalian Mutu)
            </div>
            <div class="summary-box">
                <p style="font-size: 8pt; color: #4a5568; margin-bottom: 10px; font-style: italic;">
                    Sebagai bagian dari pengendalian mutu, LSP melakukan sampling asesmen secara berkala 
                    untuk memastikan konsistensi dan objektivitas penilaian.
                    <span style="color: #718096;">(ISO 17024:2012 Clause 4.3 - Impartiality & Clause 9.4 - Internal Audits)</span>
                </p>
                <table class="data-table">
                    <tr>
                        <th style="width: 180px;">Status Sampling</th>
                        <td>
                            @if($asesmen->isSampled())
                                <span style="background: #3182ce; color: white; padding: 3px 8px; border-radius: 4px; font-size: 8pt; font-weight: bold;">
                                    ✓ SAMPLING AUDIT
                                </span>
                            @else
                                <span style="background: #a0aec0; color: white; padding: 3px 8px; border-radius: 4px; font-size: 8pt;">
                                    Tidak Disampling
                                </span>
                            @endif
                        </td>
                    </tr>
                    @if($asesmen->isSampled())
                    <tr>
                        <th>Tanggal Sampling</th>
                        <td>{{ $asesmen->sampled_at?->format('d F Y H:i') ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Ditandai Oleh</th>
                        <td>{{ $asesmen->sampledByUser?->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Catatan Sampling</th>
                        <td>{{ $asesmen->sampling_note ?: '-' }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>

        <!-- Page break before summary -->
        <div class="page-break"></div>

        <!-- Section 5: Rekap Hasil Asesmen -->
        <div class="section">
            <div class="section-title">
                <span class="section-number">5</span>
                Rekap Hasil Asesmen
            </div>
            <div class="summary-box">
                <div class="summary-grid">
                    <div class="summary-item">
                        <div class="summary-value">{{ $summary['total_unit'] }}</div>
                        <div class="summary-label">Total Unit Kompetensi</div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-value">{{ $summary['total_kuk'] }}</div>
                        <div class="summary-label">Total KUK</div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-value success">{{ $summary['total_kompeten'] }}</div>
                        <div class="summary-label">Jumlah Kompeten</div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-value danger">{{ $summary['total_belum_kompeten'] }}</div>
                        <div class="summary-label">Jumlah Belum Kompeten</div>
                    </div>
                </div>

                <table class="data-table" style="margin-top: 12px;">
                    <tr>
                        <th>Unit Kompeten</th>
                        <td>{{ $summary['unit_kompeten'] }} dari {{ $summary['total_unit'] }} unit</td>
                    </tr>
                    <tr>
                        <th>Unit Belum Kompeten</th>
                        <td>{{ $summary['unit_belum_kompeten'] }} dari {{ $summary['total_unit'] }} unit</td>
                    </tr>
                    <tr>
                        <th>Persentase Kompeten (KUK)</th>
                        <td>{{ $summary['persentase_kompeten'] }}%</td>
                    </tr>
                </table>

                <div class="conclusion-box {{ $summary['kesimpulan'] === 'LAYAK DITETAPKAN' ? 'layak' : 'tidak-layak' }}">
                    <div class="conclusion-text">
                        Kesimpulan Asesmen: {{ $summary['kesimpulan'] }}
                    </div>
                </div>
            </div>

            @if($asesmen->catatan_asesor)
            <div style="margin-top: 10px;">
                <strong style="font-size: 9pt; color: #2d3748;">Catatan Asesor:</strong>
                <div style="background: #f7fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 8px; margin-top: 4px; font-size: 9pt; color: #4a5568;">
                    {{ $asesmen->catatan_asesor }}
                </div>
            </div>
            @endif
        </div>

        <!-- Section 6: Audit Log Ringkas -->
        <div class="section">
            <div class="section-title">
                <span class="section-number">6</span>
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
                Belum ada riwayat audit log untuk asesmen ini.
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
                <div class="integrity-item">
                    <div class="integrity-label">SHA-256 Integrity Hash</div>
                    <div class="integrity-value hash-value">{{ $integrityHash }}</div>
                </div>
                <div class="integrity-item">
                    <div class="integrity-label">Waktu Generasi Dokumen</div>
                    <div class="integrity-value">{{ $generatedAt->toIso8601String() }}</div>
                </div>
                <div class="integrity-item">
                    <div class="integrity-label">Data Asesmen Terakhir Diperbarui</div>
                    <div class="integrity-value">{{ $asesmen->updated_at->toIso8601String() }}</div>
                </div>
            </div>
            <div style="font-size: 7pt; color: #718096; margin-top: 6px; font-style: italic;">
                * Hash integritas dihitung berdasarkan data asesmen, hasil penilaian, dan metadata dokumen.
                Dokumen ini bersifat non-editable dan generated by system.
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <div class="footer-content">
            <div class="footer-left">
                <div class="compliance-badge">BNSP & ISO/IEC 17024 COMPLIANT</div>
                <div class="compliance-text">
                    Dokumen ini merupakan bukti audit resmi proses asesmen kompetensi dan dihasilkan 
                    otomatis oleh sistem LSP sesuai standar BNSP dan ISO/IEC 17024. 
                    Dokumen ini menjamin pemisahan peran antara Asesor dan Komite Teknis.
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
