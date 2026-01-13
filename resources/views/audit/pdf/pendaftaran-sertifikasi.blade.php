<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Audit Evidence - {{ $pendaftaran->nomor_pendaftaran }}</title>
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
            padding: 20mm 15mm;
            min-height: 100%;
        }

        /* Header Styles */
        .header {
            border-bottom: 3px solid #1a365d;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .header-content {
            display: table;
            width: 100%;
        }

        .logo-section {
            display: table-cell;
            width: 80px;
            vertical-align: middle;
        }

        .logo-placeholder {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #1a365d 0%, #2c5282 100%);
            border-radius: 8px;
            text-align: center;
            line-height: 70px;
            color: white;
            font-weight: bold;
            font-size: 14pt;
        }

        .title-section {
            display: table-cell;
            vertical-align: middle;
            padding-left: 15px;
        }

        .main-title {
            font-size: 16pt;
            font-weight: bold;
            color: #1a365d;
            margin-bottom: 5px;
        }

        .sub-title {
            font-size: 11pt;
            color: #4a5568;
        }

        .document-info {
            display: table-cell;
            width: 200px;
            vertical-align: middle;
            text-align: right;
        }

        .doc-number {
            font-size: 9pt;
            color: #718096;
            margin-bottom: 3px;
        }

        .doc-date {
            font-size: 9pt;
            color: #718096;
        }

        /* Section Styles */
        .section {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }

        .section-title {
            background: linear-gradient(135deg, #1a365d 0%, #2c5282 100%);
            color: white;
            padding: 8px 12px;
            font-size: 11pt;
            font-weight: bold;
            margin-bottom: 10px;
            border-radius: 4px;
        }

        .section-number {
            background: rgba(255,255,255,0.2);
            padding: 2px 8px;
            border-radius: 3px;
            margin-right: 8px;
        }

        /* Table Styles */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .data-table th,
        .data-table td {
            padding: 8px 10px;
            text-align: left;
            border: 1px solid #e2e8f0;
        }

        .data-table th {
            background: #f7fafc;
            font-weight: 600;
            color: #2d3748;
            width: 35%;
        }

        .data-table td {
            color: #4a5568;
        }

        .data-table tr:nth-child(even) td {
            background: #fafafa;
        }

        /* Timeline Styles */
        .timeline {
            position: relative;
            padding-left: 30px;
        }

        .timeline-item {
            position: relative;
            padding-bottom: 15px;
            border-left: 2px solid #cbd5e0;
            padding-left: 25px;
            margin-left: 10px;
        }

        .timeline-item:last-child {
            border-left: 2px solid transparent;
            padding-bottom: 0;
        }

        .timeline-dot {
            position: absolute;
            left: -9px;
            top: 0;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: #cbd5e0;
            border: 3px solid white;
            box-shadow: 0 0 0 2px #cbd5e0;
        }

        .timeline-dot.completed {
            background: #48bb78;
            box-shadow: 0 0 0 2px #48bb78;
        }

        .timeline-dot.in_progress {
            background: #4299e1;
            box-shadow: 0 0 0 2px #4299e1;
        }

        .timeline-dot.rejected {
            background: #f56565;
            box-shadow: 0 0 0 2px #f56565;
        }

        .timeline-dot.pending {
            background: #a0aec0;
            box-shadow: 0 0 0 2px #a0aec0;
        }

        .timeline-label {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 3px;
        }

        .timeline-date {
            font-size: 9pt;
            color: #718096;
            margin-bottom: 3px;
        }

        .timeline-desc {
            font-size: 9pt;
            color: #4a5568;
        }

        /* Audit Log Table */
        .audit-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
        }

        .audit-table th {
            background: #2d3748;
            color: white;
            padding: 8px;
            text-align: left;
            font-weight: 600;
        }

        .audit-table td {
            padding: 6px 8px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: top;
        }

        .audit-table tr:nth-child(even) td {
            background: #f7fafc;
        }

        .action-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8pt;
            font-weight: 600;
            text-transform: uppercase;
        }

        .action-create { background: #c6f6d5; color: #22543d; }
        .action-update { background: #bee3f8; color: #2a4365; }
        .action-view { background: #e9d8fd; color: #44337a; }
        .action-approve { background: #c6f6d5; color: #22543d; }
        .action-reject { background: #fed7d7; color: #742a2a; }
        .action-verify { background: #fefcbf; color: #744210; }

        /* Integrity Section */
        .integrity-box {
            background: #f7fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 15px;
        }

        .integrity-item {
            margin-bottom: 10px;
        }

        .integrity-item:last-child {
            margin-bottom: 0;
        }

        .integrity-label {
            font-size: 9pt;
            color: #718096;
            margin-bottom: 3px;
        }

        .integrity-value {
            font-family: 'DejaVu Sans Mono', monospace;
            font-size: 9pt;
            color: #2d3748;
            background: white;
            padding: 5px 8px;
            border-radius: 3px;
            border: 1px solid #e2e8f0;
            word-break: break-all;
        }

        .hash-value {
            font-size: 8pt;
            letter-spacing: 0.5px;
        }

        /* Footer Styles */
        .footer {
            position: fixed;
            bottom: 15mm;
            left: 15mm;
            right: 15mm;
            border-top: 2px solid #1a365d;
            padding-top: 10px;
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
            font-size: 8pt;
            color: #718096;
            line-height: 1.5;
        }

        .compliance-badge {
            display: inline-block;
            background: #1a365d;
            color: white;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 8pt;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .page-number {
            font-size: 9pt;
            color: #718096;
        }

        /* Status Badges */
        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 9pt;
            font-weight: 600;
        }

        .status-draft { background: #e2e8f0; color: #4a5568; }
        .status-diajukan { background: #fefcbf; color: #744210; }
        .status-diverifikasi { background: #bee3f8; color: #2a4365; }
        .status-ditolak { background: #fed7d7; color: #742a2a; }
        .status-siap_asesmen { background: #c6f6d5; color: #22543d; }
        .status-menunggu_keputusan { background: #e9d8fd; color: #44337a; }
        .status-kompeten_final { background: #9ae6b4; color: #1c4532; }
        .status-belum_kompeten_final { background: #feb2b2; color: #63171b; }

        /* Unit Kompetensi List */
        .uk-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .uk-item {
            padding: 8px 10px;
            border-bottom: 1px solid #e2e8f0;
            display: table;
            width: 100%;
        }

        .uk-item:last-child {
            border-bottom: none;
        }

        .uk-code {
            display: table-cell;
            width: 150px;
            font-weight: 600;
            color: #2d3748;
            font-size: 9pt;
        }

        .uk-title {
            display: table-cell;
            color: #4a5568;
            font-size: 9pt;
        }

        /* Notice Box */
        .notice-box {
            background: #fefcbf;
            border-left: 4px solid #d69e2e;
            padding: 10px 15px;
            margin: 10px 0;
            font-size: 9pt;
            color: #744210;
        }

        /* Page break control */
        .page-break {
            page-break-before: always;
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
                    <div class="main-title">BUKTI AUDIT PENDAFTARAN SERTIFIKASI</div>
                    @if($logoInfo['nama_perusahaan'])
                        <div class="sub-title">{{ $logoInfo['nama_perusahaan'] }}</div>
                    @else
                        <div class="sub-title">Lembaga Sertifikasi Profesi</div>
                    @endif
                </div>
                <div class="document-info">
                    <div class="doc-number"><strong>No. Dokumen:</strong> {{ $documentNumber }}</div>
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
                    <th>Nomor Pendaftaran</th>
                    <td><strong>{{ $pendaftaran->nomor_pendaftaran }}</strong></td>
                </tr>
                <tr>
                    <th>Nama Lengkap</th>
                    <td>{{ $pendaftaran->asesi_name }}</td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td>{{ $pendaftaran->asesi_email }}</td>
                </tr>
                <tr>
                    <th>No. HP</th>
                    <td>{{ $pendaftaran->no_hp ?? $pendaftaran->praPendaftaran?->no_hp ?? '-' }}</td>
                </tr>
                <tr>
                    <th>NIK</th>
                    <td>{{ $pendaftaran->nik ?? $pendaftaran->praPendaftaran?->nik ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Tipe Peserta</th>
                    <td>{{ ucfirst($pendaftaran->tipe_peserta ?? $pendaftaran->praPendaftaran?->tipe_peserta ?? '-') }}</td>
                </tr>
                @if($pendaftaran->tipe_peserta === 'mahasiswa' || $pendaftaran->praPendaftaran?->tipe_peserta === 'mahasiswa')
                <tr>
                    <th>NIM</th>
                    <td>{{ $pendaftaran->nim ?? $pendaftaran->praPendaftaran?->nim ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Institusi</th>
                    <td>{{ $pendaftaran->institusi ?? $pendaftaran->praPendaftaran?->institusi ?? '-' }}</td>
                </tr>
                @endif
                <tr>
                    <th>Tanggal Pendaftaran</th>
                    <td>{{ $pendaftaran->created_at->translatedFormat('d F Y, H:i') }} WIB</td>
                </tr>
                @if($pendaftaran->praPendaftaran)
                <tr>
                    <th>Nomor Pra-Pendaftaran</th>
                    <td>{{ $pendaftaran->praPendaftaran->nomor_pra_pendaftaran ?? '-' }}</td>
                </tr>
                @endif
            </table>
        </div>

        <!-- Section 2: Informasi Skema Sertifikasi -->
        <div class="section">
            <div class="section-title">
                <span class="section-number">2</span>
                Informasi Skema Sertifikasi
            </div>
            @if($pendaftaran->skemaSertifikasi)
            <table class="data-table">
                <tr>
                    <th>Kode Skema</th>
                    <td><strong>{{ $pendaftaran->skemaSertifikasi->kode_skema }}</strong></td>
                </tr>
                <tr>
                    <th>Nama Skema</th>
                    <td>{{ $pendaftaran->skemaSertifikasi->nama_skema }}</td>
                </tr>
                <tr>
                    <th>KKNI Level</th>
                    <td>{{ $pendaftaran->skemaSertifikasi->kkni_level ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Status Skema</th>
                    <td>{{ $pendaftaran->skemaSertifikasi->aktif ? 'Aktif' : 'Tidak Aktif' }}</td>
                </tr>
            </table>
            
            @if($pendaftaran->skemaSertifikasi->unitKompetensi && $pendaftaran->skemaSertifikasi->unitKompetensi->count() > 0)
            <div style="margin-top: 10px;">
                <strong style="color: #2d3748; font-size: 10pt;">Unit Kompetensi ({{ $pendaftaran->skemaSertifikasi->unitKompetensi->count() }} unit):</strong>
                <ul class="uk-list" style="margin-top: 8px; border: 1px solid #e2e8f0; border-radius: 4px;">
                    @foreach($pendaftaran->skemaSertifikasi->unitKompetensi as $uk)
                    <li class="uk-item">
                        <span class="uk-code">{{ $uk->kode_unit }}</span>
                        <span class="uk-title">{{ $uk->judul_unit }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif
            @else
            <div class="notice-box">
                <strong>Perhatian:</strong> Skema sertifikasi belum ditetapkan untuk pendaftaran ini.
            </div>
            @endif
        </div>

        <!-- Section 3: Status Pendaftaran (Timeline) -->
        <div class="section">
            <div class="section-title">
                <span class="section-number">3</span>
                Status Pendaftaran
            </div>
            <table class="data-table" style="margin-bottom: 15px;">
                <tr>
                    <th>Status Saat Ini</th>
                    <td>
                        <span class="status-badge status-{{ $pendaftaran->status }}">
                            {{ $pendaftaran->status_label }}
                        </span>
                    </td>
                </tr>
                @if($pendaftaran->catatan_admin)
                <tr>
                    <th>Catatan Admin</th>
                    <td>{{ $pendaftaran->catatan_admin }}</td>
                </tr>
                @endif
            </table>

            <strong style="color: #2d3748; font-size: 10pt;">Timeline Proses:</strong>
            <div class="timeline" style="margin-top: 10px;">
                @foreach($statusTimeline as $item)
                <div class="timeline-item">
                    <div class="timeline-dot {{ $item['status'] }}"></div>
                    <div class="timeline-label">{{ $item['label'] }}</div>
                    @if($item['date'])
                    <div class="timeline-date">{{ $item['date'] instanceof \Carbon\Carbon ? $item['date']->translatedFormat('d M Y, H:i') : $item['date'] }} WIB</div>
                    @else
                    <div class="timeline-date">-</div>
                    @endif
                    <div class="timeline-desc">{{ $item['description'] }}</div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Page break before audit log if needed -->
        @if(count($statusTimeline) > 4)
        <div class="page-break"></div>
        @endif

        <!-- Section 4: Audit Log Ringkas -->
        <div class="section">
            <div class="section-title">
                <span class="section-number">4</span>
                Riwayat Audit Log
            </div>
            @if($auditLogs->count() > 0)
            <table class="audit-table">
                <thead>
                    <tr>
                        <th style="width: 130px;">Waktu</th>
                        <th style="width: 80px;">Aksi</th>
                        <th style="width: 100px;">Pengguna</th>
                        <th>Deskripsi</th>
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
                        <td>{{ $log->user_name }}</td>
                        <td>{{ Str::limit($log->description, 100) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div style="font-size: 8pt; color: #718096; margin-top: 5px; text-align: right;">
                Menampilkan {{ $auditLogs->count() }} log terakhir
            </div>
            @else
            <div class="notice-box">
                Belum ada riwayat audit log untuk pendaftaran ini.
            </div>
            @endif
        </div>

        <!-- Section 5: Integritas Data -->
        <div class="section">
            <div class="section-title">
                <span class="section-number">5</span>
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
                    <div class="integrity-label">Data Terakhir Diperbarui</div>
                    <div class="integrity-value">{{ $pendaftaran->updated_at->toIso8601String() }}</div>
                </div>
            </div>
            <div style="font-size: 8pt; color: #718096; margin-top: 8px; font-style: italic;">
                * Hash integritas dapat digunakan untuk memverifikasi keaslian dokumen ini. 
                Hash dihitung berdasarkan ID pendaftaran, nomor pendaftaran, status, timestamp, dan UUID dokumen.
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <div class="footer-content">
            <div class="footer-left">
                <div class="compliance-badge">BNSP & ISO 17024 COMPLIANT</div>
                <div class="compliance-text">
                    Dokumen ini diterbitkan secara elektronik oleh sistem LSP dan merupakan bukti resmi 
                    untuk keperluan audit, arsip, dan dokumentasi proses sertifikasi kompetensi. Dokumen ini 
                    dapat diverifikasi melalui sistem verifikasi online LSP.
                </div>
            </div>
            <div class="footer-right">
                <div class="page-number">
                    Halaman 1
                </div>
                <div style="font-size: 8pt; color: #a0aec0; margin-top: 5px;">
                    {{ $documentNumber }}
                </div>
            </div>
        </div>
    </div>
</body>
</html>
