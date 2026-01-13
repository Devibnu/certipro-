{{--
================================================================================
CertiPro LSP - Base Email Layout Template
================================================================================
File: resources/views/emails/layout.blade.php
Purpose: Base layout untuk semua email transaksional LSP
Compliance: BNSP, ISO 17024
Version: 1.0.0
================================================================================
--}}

<!DOCTYPE html>
<html lang="id" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="x-apple-disable-message-reformatting">
    <meta name="format-detection" content="telephone=no,address=no,email=no,date=no,url=no">
    @php $systemName = systemCompanyName(); $logoBase64 = systemLogoBase64(); @endphp
    <title>@yield('title', 'Notifikasi ' . ($systemName ?? 'LSP'))</title>
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:AllowPNG/>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->
</head>
<body style="margin: 0; padding: 0; width: 100%; background-color: #F5F7FA; font-family: Arial, Helvetica, sans-serif; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;">
    
    <!-- Preheader Text (Hidden) -->
    <div style="display: none; max-height: 0; overflow: hidden; mso-hide: all;">
        @yield('preheader', 'Notifikasi resmi dari ' . ($systemName ?? 'LSP'))
        &#847; &zwnj; &nbsp; &#8199; &shy; &#847; &zwnj; &nbsp; &#8199; &shy; &#847; &zwnj; &nbsp; &#8199; &shy;
    </div>

    <!-- Email Wrapper Table -->
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color: #F5F7FA;">
        <tr>
            <td align="center" style="padding: 24px 16px;">
                
                <!-- Main Email Container -->
                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="600" style="max-width: 600px; width: 100%; background-color: #FFFFFF; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                    
                    <!-- ============================================ -->
                    <!-- HEADER SECTION -->
                    <!-- ============================================ -->
                    <tr>
                        <td align="center" style="padding: 32px 40px 24px 40px; background: linear-gradient(135deg, #0D47A1 0%, #1565C0 100%); border-radius: 8px 8px 0 0;">
                            <!-- Logo -->
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="center">
                                        @if($logoBase64)
                                            <img src="{{ $logoBase64 }}" alt="Logo LSP" width="120" height="auto" style="display: block; max-width: 120px; height: auto; border: 0;">
                                        @elseif(config('app.logo_url'))
                                            <img src="{{ config('app.logo_url') }}" alt="Logo LSP" width="120" height="auto" style="display: block; max-width: 120px; height: auto; border: 0;">
                                        @else
                                            <!-- Text Logo Fallback -->
                                            <div style="width: 80px; height: 80px; background-color: #FFFFFF; border-radius: 50%; display: inline-block; text-align: center; line-height: 80px;">
                                                <span style="font-size: 32px; font-weight: bold; color: #0D47A1;">CP</span>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center" style="padding-top: 16px;">
                                        <h1 style="margin: 0; font-size: 24px; font-weight: bold; color: #FFFFFF; letter-spacing: 1px;">
                                            {{ $systemName ?? 'LSP' }}
                                        </h1>
                                        <p style="margin: 4px 0 0 0; font-size: 14px; color: rgba(255,255,255,0.85); font-weight: 500;">
                                            Lembaga Sertifikasi Profesi
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- ============================================ -->
                    <!-- STATUS BADGE SECTION (Optional) -->
                    <!-- ============================================ -->
                    @hasSection('status_badge')
                    <tr>
                        <td align="center" style="padding: 24px 40px 0 40px;">
                            @yield('status_badge')
                        </td>
                    </tr>
                    @endif

                    <!-- ============================================ -->
                    <!-- MAIN CONTENT SECTION -->
                    <!-- ============================================ -->
                    <tr>
                        <td style="padding: 32px 40px;">
                            @yield('content')
                        </td>
                    </tr>

                    <!-- ============================================ -->
                    <!-- CALL TO ACTION SECTION (Optional) -->
                    <!-- ============================================ -->
                    @hasSection('cta')
                    <tr>
                        <td align="center" style="padding: 0 40px 32px 40px;">
                            @yield('cta')
                        </td>
                    </tr>
                    @endif

                    <!-- ============================================ -->
                    <!-- REFERENCE INFO SECTION -->
                    <!-- ============================================ -->
                    <tr>
                        <td style="padding: 0 40px 24px 40px;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color: #F8FAFC; border-radius: 6px; border: 1px solid #E2E8F0;">
                                <tr>
                                    <td style="padding: 16px 20px;">
                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                                            <tr>
                                                <td style="font-size: 12px; color: #64748B; padding-bottom: 8px;">
                                                    <strong style="color: #475569;">Referensi:</strong>
                                                    <span style="font-family: 'Courier New', monospace; color: #0D47A1;">
                                                        @yield('reference_number', 'N/A')
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="font-size: 12px; color: #64748B;">
                                                    <strong style="color: #475569;">Timestamp:</strong>
                                                    <span>{{ now()->setTimezone('Asia/Jakarta')->format('d M Y, H:i:s') }} WIB</span>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- ============================================ -->
                    <!-- DIVIDER -->
                    <!-- ============================================ -->
                    <tr>
                        <td style="padding: 0 40px;">
                            <div style="height: 1px; background-color: #E2E8F0;"></div>
                        </td>
                    </tr>

                    <!-- ============================================ -->
                    <!-- FOOTER SECTION -->
                    <!-- ============================================ -->
                    <tr>
                        <td style="padding: 24px 40px 32px 40px;">
                            <!-- Footer Content -->
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                                <tr>
                                    <td align="center" style="padding-bottom: 16px;">
                                        <p style="margin: 0; font-size: 14px; color: #64748B; line-height: 1.6;">
                                            Email ini dikirim secara otomatis oleh sistem resmi LSP.<br>
                                            <strong style="color: #475569;">Mohon tidak membalas email ini.</strong>
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center" style="padding-bottom: 16px;">
                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td style="padding: 0 8px;">
                                                    <a href="{{ config('app.url') }}" style="font-size: 13px; color: #0D47A1; text-decoration: none;">
                                                        Website
                                                    </a>
                                                </td>
                                                <td style="color: #CBD5E1;">|</td>
                                                <td style="padding: 0 8px;">
                                                    <a href="{{ config('app.url') }}/kontak" style="font-size: 13px; color: #0D47A1; text-decoration: none;">
                                                        Kontak
                                                    </a>
                                                </td>
                                                <td style="color: #CBD5E1;">|</td>
                                                <td style="padding: 0 8px;">
                                                    <a href="{{ config('app.url') }}/faq" style="font-size: 13px; color: #0D47A1; text-decoration: none;">
                                                        FAQ
                                                    </a>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center">
                                        <p style="margin: 0; font-size: 12px; color: #94A3B8; line-height: 1.5;">
                                            &copy; {{ date('Y') }} {{ $systemName ?? 'LSP' }} - Lembaga Sertifikasi Profesi<br>
                                            Terakreditasi BNSP | ISO 17024 Compliant
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                </table>
                <!-- End Main Email Container -->

                <!-- Legal Disclaimer -->
                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="600" style="max-width: 600px; width: 100%;">
                    <tr>
                        <td align="center" style="padding: 24px 16px;">
                            <p style="margin: 0; font-size: 11px; color: #94A3B8; line-height: 1.5; text-align: center;">
                                Email ini dan lampirannya bersifat rahasia dan hanya ditujukan kepada penerima yang tercantum.
                                Jika Anda menerima email ini secara tidak sengaja, mohon segera hapus dan beritahu pengirim.
                                Dilarang menyebarluaskan, menyalin, atau menggunakan isi email ini tanpa izin tertulis.
                            </p>
                        </td>
                    </tr>
                </table>

            </td>
        </tr>
    </table>
    <!-- End Email Wrapper -->

</body>
</html>
