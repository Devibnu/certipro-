<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    @php $logoInfo = systemLogoInfo(); @endphp
    <title>@yield('title', 'Dokumen PDF' . ($logoInfo['nama_perusahaan'] ? ' - ' . $logoInfo['nama_perusahaan'] : ''))</title>
    <style>
        /* ================================================================
           BASE LAYOUT PDF
           Template global untuk semua dokumen PDF resmi
           Standar: BNSP / ISO 17024
           ================================================================ */
        
        @page {
            size: A4 portrait;
            margin: @yield('page-margin', '15mm 15mm 20mm 15mm');
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10pt;
            color: #1a1a1a;
            line-height: 1.5;
            background: #ffffff;
        }
        
        /* ================================================================
           HEADER - LOGO SECTION
           ================================================================ */
        
        .pdf-header {
            display: table;
            width: 100%;
            margin-bottom: 10mm;
            padding-bottom: 5mm;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .pdf-header-logo {
            display: table-cell;
            vertical-align: middle;
            width: 30%;
        }
        
        .pdf-header-logo img {
            max-height: 50px;
            max-width: 150px;
            height: auto;
        }
        
        .pdf-header-title {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
            width: 70%;
        }
        
        .pdf-header-title h1 {
            font-size: 14pt;
            font-weight: bold;
            color: #1a365d;
            margin: 0 0 2mm 0;
        }
        
        .pdf-header-title p {
            font-size: 9pt;
            color: #64748b;
            margin: 0;
        }
        
        /* ================================================================
           FOOTER - COMPLIANCE
           ================================================================ */
        
        .pdf-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 5mm 15mm;
            border-top: 1px solid #e5e7eb;
            font-size: 8pt;
            color: #64748b;
            background: #ffffff;
        }
        
        .pdf-footer-table {
            width: 100%;
        }
        
        .pdf-footer-left {
            text-align: left;
        }
        
        .pdf-footer-center {
            text-align: center;
        }
        
        .pdf-footer-right {
            text-align: right;
        }
        
        .compliance-badge {
            display: inline-block;
            padding: 1mm 3mm;
            background: #f1f5f9;
            border-radius: 2mm;
            font-size: 7pt;
            color: #475569;
            margin-left: 2mm;
        }
        
        /* ================================================================
           CONTENT AREA
           ================================================================ */
        
        .pdf-content {
            margin-bottom: 20mm;
        }
        
        /* ================================================================
           WATERMARK
           ================================================================ */
        
        .watermark {
            position: fixed;
            top: 40%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 60pt;
            color: rgba(0, 0, 0, 0.03);
            font-weight: bold;
            white-space: nowrap;
            z-index: -1;
            pointer-events: none;
        }
        
        /* ================================================================
           PAGE BREAK
           ================================================================ */
        
        .page-break {
            page-break-after: always;
        }
        
        .page-break-before {
            page-break-before: always;
        }
        
        .avoid-break {
            page-break-inside: avoid;
        }
        
        /* ================================================================
           ADDITIONAL STYLES
           ================================================================ */
        
        @yield('additional-styles')
    </style>
</head>
<body>
    {{-- Watermark --}}
    @hasSection('watermark')
        <div class="watermark">@yield('watermark')</div>
    @endif
    
    {{-- Header --}}
    @hasSection('hide-header')
    @else
        <div class="pdf-header">
            <div class="pdf-header-logo">
                @php
                    $logoInfo = systemLogoInfo();
                    $logoBase64 = systemLogoBase64();
                @endphp
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" alt="{{ $logoInfo['nama_perusahaan'] }}">
                @endif
            </div>
            <div class="pdf-header-title">
                <h1>@yield('document-title', 'Dokumen Resmi')</h1>
                @if($logoInfo['nama_perusahaan'])
                    <p>@yield('document-subtitle', $logoInfo['nama_perusahaan'])</p>
                @else
                    <p>@yield('document-subtitle', 'Lembaga Sertifikasi Profesi')</p>
                @endif
            </div>
        </div>
    @endif
    
    {{-- Main Content --}}
    <div class="pdf-content">
        @yield('content')
    </div>
    
    {{-- Footer --}}
    @hasSection('hide-footer')
    @else
        <div class="pdf-footer">
            <table class="pdf-footer-table">
                <tr>
                    <td class="pdf-footer-left" style="width: 33%;">
                        @php
                            $logoInfo = systemLogoInfo();
                        @endphp
                        @if($logoInfo['nama_perusahaan'])
                            {{ $logoInfo['nama_perusahaan'] }}
                        @else
                            Lembaga Sertifikasi Profesi
                        @endif
                    </td>
                    <td class="pdf-footer-center" style="width: 34%;">
                        <span class="compliance-badge">BNSP</span>
                        <span class="compliance-badge">ISO 17024</span>
                    </td>
                    <td class="pdf-footer-right" style="width: 33%;">
                        Dicetak: {{ now()->format('d/m/Y H:i') }}
                    </td>
                </tr>
            </table>
        </div>
    @endif
</body>
</html>
