<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Evidence - Email & Notifikasi</title>
    <style>
        /* ========================================================
           CSS Styles for Audit Evidence PDF
           ======================================================== */
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.4;
            color: #1a1a1a;
            background: #fff;
        }

        .page {
            position: relative;
            padding: 20px 25px;
            min-height: 100%;
        }

        /* Watermark */
        .watermark {
            position: fixed;
            top: 40%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-35deg);
            font-size: 48pt;
            color: rgba(0, 0, 0, 0.04);
            font-weight: bold;
            letter-spacing: 5px;
            white-space: nowrap;
            z-index: 0;
            pointer-events: none;
        }

        /* Header Section */
        .header {
            text-align: center;
            padding-bottom: 15px;
            border-bottom: 2px solid #0d6efd;
            margin-bottom: 20px;
        }

        .header-logo {
            font-size: 16pt;
            font-weight: bold;
            color: #0d6efd;
            margin-bottom: 5px;
        }

        .header-title {
            font-size: 14pt;
            font-weight: bold;
            color: #1a1a1a;
            margin-bottom: 3px;
        }

        .header-subtitle {
            font-size: 9pt;
            color: #666;
        }

        /* Document ID Box */
        .document-box {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 12px 15px;
            margin-bottom: 20px;
        }

        .document-row {
            display: table;
            width: 100%;
            margin-bottom: 6px;
        }

        .document-row:last-child {
            margin-bottom: 0;
        }

        .document-label {
            display: table-cell;
            width: 35%;
            font-weight: 600;
            color: #495057;
            font-size: 9pt;
        }

        .document-value {
            display: table-cell;
            color: #1a1a1a;
            font-size: 9pt;
        }

        /* Section Styling */
        .section {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }

        .section-title {
            font-size: 11pt;
            font-weight: bold;
            color: #0d6efd;
            border-bottom: 1px solid #0d6efd;
            padding-bottom: 5px;
            margin-bottom: 12px;
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
            width: 30%;
            padding: 5px 10px 5px 0;
            font-weight: 600;
            color: #495057;
            vertical-align: top;
        }

        .info-value {
            display: table-cell;
            padding: 5px 0;
            color: #1a1a1a;
        }

        /* Summary Box */
        .summary-box {
            background: #e7f1ff;
            border: 1px solid #b6d4fe;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .summary-grid {
            display: table;
            width: 100%;
        }

        .summary-item {
            display: table-cell;
            text-align: center;
            padding: 5px;
        }

        .summary-number {
            font-size: 18pt;
            font-weight: bold;
            color: #0d6efd;
        }

        .summary-number.success {
            color: #198754;
        }

        .summary-number.warning {
            color: #ffc107;
        }

        .summary-number.danger {
            color: #dc3545;
        }

        .summary-label {
            font-size: 8pt;
            color: #666;
            margin-top: 2px;
        }

        /* Table Styling */
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 8pt;
        }

        .table th {
            background: #0d6efd;
            color: #fff;
            font-weight: 600;
            padding: 8px 6px;
            text-align: left;
            border: 1px solid #0d6efd;
        }

        .table td {
            padding: 6px;
            border: 1px solid #dee2e6;
            vertical-align: top;
        }

        .table tr:nth-child(even) td {
            background: #f8f9fa;
        }

        .table tr:hover td {
            background: #e9ecef;
        }

        /* Status Badges */
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 7pt;
            font-weight: bold;
        }

        .badge-success {
            background: #d1e7dd;
            color: #0f5132;
        }

        .badge-danger {
            background: #f8d7da;
            color: #842029;
        }

        .badge-warning {
            background: #fff3cd;
            color: #664d03;
        }

        .badge-info {
            background: #cff4fc;
            color: #055160;
        }

        /* Integrity Section */
        .integrity-box {
            background: #f8f9fa;
            border: 2px dashed #6c757d;
            border-radius: 4px;
            padding: 15px;
            margin-top: 20px;
        }

        .integrity-title {
            font-size: 10pt;
            font-weight: bold;
            color: #495057;
            margin-bottom: 10px;
        }

        .hash-value {
            font-family: 'DejaVu Sans Mono', monospace;
            font-size: 7pt;
            background: #e9ecef;
            padding: 8px;
            border-radius: 3px;
            word-break: break-all;
            color: #495057;
        }

        .integrity-info {
            margin-top: 10px;
            font-size: 8pt;
            color: #6c757d;
        }

        /* Footer */
        .footer {
            position: fixed;
            bottom: 20px;
            left: 25px;
            right: 25px;
            font-size: 7pt;
            color: #6c757d;
            border-top: 1px solid #dee2e6;
            padding-top: 10px;
        }

        .footer-left {
            float: left;
        }

        .footer-right {
            float: right;
            text-align: right;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 30px;
            color: #6c757d;
        }

        .empty-state-icon {
            font-size: 24pt;
            margin-bottom: 10px;
        }

        /* Notes */
        .note-text {
            font-size: 7pt;
            color: #6c757d;
            font-style: italic;
        }

        /* Page Break */
        .page-break {
            page-break-after: always;
        }

        /* Utility */
        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-muted {
            color: #6c757d;
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
    </style>
</head>
<body>
    <!-- Watermark -->
    <div class="watermark">CONFIDENTIAL – AUDIT LSP</div>

    <div class="page">
        <!-- Header -->
        @php
            $logoBase64 = systemLogoBase64();
            $logoInfo = systemLogoInfo();
        @endphp
        <div class="header">
            @if($logoBase64)
                <img src="{{ $logoBase64 }}" alt="Logo" style="max-width: 60px; max-height: 50px; margin-bottom: 5px;">
            @else
                @if($logoInfo['nama_perusahaan'])
                    <div class="header-logo">{{ $logoInfo['nama_perusahaan'] }}</div>
                @else
                    <div class="header-logo">LSP</div>
                @endif
            @endif
            <div class="header-title">BUKTI AUDIT – EMAIL & NOTIFIKASI</div>
            <div class="header-subtitle">Dokumen ini digenerate secara otomatis sebagai bukti audit komunikasi email</div>
        </div>

        <!-- Document Information -->
        <div class="document-box">
            <div class="document-row">
                <div class="document-label">Nomor Dokumen</div>
                <div class="document-value"><strong>{{ $document['document_number'] }}</strong></div>
            </div>
            <div class="document-row">
                <div class="document-label">Jenis Audit</div>
                <div class="document-value">{{ $document['audit_type'] }}</div>
            </div>
            <div class="document-row">
                <div class="document-label">Referensi</div>
                <div class="document-value">{{ $document['reference_type'] }}: <strong>{{ $document['reference_number'] }}</strong></div>
            </div>
            <div class="document-row">
                <div class="document-label">Tanggal Cetak</div>
                <div class="document-value">{{ $document['print_date'] }} – {{ $document['print_time'] }}</div>
            </div>
        </div>

        <!-- Data Peserta Section -->
        <div class="section">
            <div class="section-title">Data Peserta</div>
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-label">Nama Lengkap</div>
                    <div class="info-value">{{ $peserta['nama'] }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Email</div>
                    <div class="info-value">{{ $peserta['email'] }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">No. Identitas</div>
                    <div class="info-value">{{ $peserta['identitas'] }}</div>
                </div>
            </div>
        </div>

        <!-- Email Summary Section -->
        <div class="summary-box">
            <div class="section-title mb-0" style="border: none; padding: 0; margin-bottom: 10px;">Ringkasan Notifikasi Email</div>
            <div class="summary-grid">
                <div class="summary-item">
                    <div class="summary-number">{{ $emailSummary['total_events'] }}</div>
                    <div class="summary-label">Total Events</div>
                </div>
                <div class="summary-item">
                    <div class="summary-number success">{{ $emailSummary['sent_count'] }}</div>
                    <div class="summary-label">Terkirim (Auto)</div>
                </div>
                <div class="summary-item">
                    <div class="summary-number warning">{{ $emailSummary['resent_count'] }}</div>
                    <div class="summary-label">Kirim Ulang</div>
                </div>
                <div class="summary-item">
                    <div class="summary-number danger">{{ $emailSummary['failed_count'] }}</div>
                    <div class="summary-label">Gagal</div>
                </div>
                <div class="summary-item">
                    <div class="summary-number">{{ $emailSummary['previewed_count'] }}</div>
                    <div class="summary-label">Preview</div>
                </div>
            </div>
            @if($emailSummary['first_sent'] || $emailSummary['last_sent'])
            <div style="margin-top: 10px; font-size: 8pt; color: #495057;">
                @if($emailSummary['first_sent'])
                    <strong>Pertama Dikirim:</strong> {{ $emailSummary['first_sent'] }}
                @endif
                @if($emailSummary['first_sent'] && $emailSummary['last_sent'])
                    &nbsp;|&nbsp;
                @endif
                @if($emailSummary['last_sent'])
                    <strong>Terakhir Dikirim:</strong> {{ $emailSummary['last_sent'] }}
                @endif
            </div>
            @endif
        </div>

        <!-- Email Audit Log Section -->
        <div class="section">
            <div class="section-title">Riwayat Email (Audit Log)</div>
            
            @if(count($emailLogs) > 0)
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 5%;">No</th>
                        <th style="width: 18%;">Waktu</th>
                        <th style="width: 20%;">Event</th>
                        <th style="width: 18%;">Template</th>
                        <th style="width: 18%;">Penerima</th>
                        <th style="width: 12%;">Oleh</th>
                        <th style="width: 9%;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($emailLogs as $index => $log)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $log['timestamp'] }}</td>
                        <td>{{ $log['event'] }}</td>
                        <td>{{ $log['template'] }}</td>
                        <td style="word-break: break-all;">{{ $log['email_to'] }}</td>
                        <td>
                            {{ $log['user'] }}
                            <br>
                            <span class="text-muted">({{ $log['user_role'] }})</span>
                        </td>
                        <td class="text-center">
                            @if($log['is_success'])
                                <span class="badge badge-success">OK</span>
                            @else
                                <span class="badge badge-danger">GAGAL</span>
                            @endif
                        </td>
                    </tr>
                    @if($log['notes'])
                    <tr>
                        <td></td>
                        <td colspan="6" class="note-text">
                            <strong>Catatan:</strong> {{ $log['notes'] }}
                        </td>
                    </tr>
                    @endif
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="empty-state">
                <div class="empty-state-icon">📭</div>
                <div>Tidak ada riwayat email untuk referensi ini.</div>
            </div>
            @endif
        </div>

        <!-- Templates Used Section -->
        @if(count($emailSummary['templates']) > 0)
        <div class="section">
            <div class="section-title">Template Email yang Digunakan</div>
            <ul style="margin-left: 20px;">
                @foreach($emailSummary['templates'] as $template)
                <li>{{ $template }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- Integrity Section -->
        <div class="integrity-box">
            <div class="integrity-title">🔒 Integritas Dokumen</div>
            <div class="hash-value">
                <strong>SHA-256:</strong> {{ $integrity['hash'] }}
            </div>
            <div class="integrity-info">
                <div><strong>UUID:</strong> {{ $integrity['uuid'] }}</div>
                <div><strong>Digenerate:</strong> {{ $integrity['generated_at'] }} oleh {{ $integrity['generated_by'] }} ({{ $integrity['generated_by_role'] }})</div>
            </div>
            <div style="margin-top: 10px; font-size: 7pt; color: #6c757d; font-style: italic;">
                * Hash ini digunakan untuk memverifikasi integritas dokumen. Modifikasi apapun pada dokumen akan menghasilkan hash berbeda.
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="footer-left">
                @if($logoInfo['nama_perusahaan'])
                    {{ $logoInfo['nama_perusahaan'] }} – Sistem Manajemen Sertifikasi LSP
                @else
                    Sistem Manajemen Sertifikasi LSP
                @endif
            </div>
            <div class="footer-right">
                Dokumen ini digenerate otomatis dan valid tanpa tanda tangan.
            </div>
        </div>
    </div>
</body>
</html>
