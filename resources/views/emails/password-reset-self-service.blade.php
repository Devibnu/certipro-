@php
/**
 * ============================================================================
 * CertiPro LSP - Password Reset Self-Service Email Template
 * ============================================================================
 * Gmail-compatible | Table-based | Inline CSS | Max 600px
 * Compliance: ISO 27001, ISO 17024, BNSP
 * Variables: $nama, $resetLink, $expiresInMinutes
 * ============================================================================
 */
$systemName = systemCompanyName() ?? 'CertiPro';
$logoUrl = systemLogoUrl();
$currentYear = date('Y');
@endphp
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="id">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="x-apple-disable-message-reformatting" />
<meta name="format-detection" content="telephone=no, date=no, address=no, email=no" />
<title>Reset Password - {{ $systemName }}</title>
<!--[if mso]>
<style type="text/css">
table {border-collapse: collapse;}
td {font-family: Arial, Helvetica, sans-serif;}
</style>
<![endif]-->
</head>
<body style="margin: 0; padding: 0; width: 100%; background-color: #f3f4f6; font-family: Arial, Helvetica, sans-serif; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%;">

<!-- WRAPPER TABLE -->
<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color: #f3f4f6;">
<tr>
<td align="center" style="padding: 30px 15px;">

<!-- MAIN CONTAINER 600px -->
<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="600" style="max-width: 600px; width: 100%; background-color: #ffffff; border-radius: 8px; overflow: hidden;">

<!-- ===== HEADER ===== -->
<tr>
<td align="center" style="padding: 35px 40px 25px 40px; background-color: #ffffff; border-bottom: 3px solid #2563eb;">
    <table role="presentation" cellpadding="0" cellspacing="0" border="0">
    <tr>
    <td align="center">
        @if($logoUrl)
        <img src="{{ $logoUrl }}" alt="{{ $systemName }}" width="70" height="70" style="display: block; border: 0; outline: none; max-width: 70px; height: auto;" />
        @else
        <table role="presentation" cellpadding="0" cellspacing="0" border="0">
        <tr>
        <td align="center" valign="middle" style="width: 60px; height: 60px; background-color: #2563eb; border-radius: 12px;">
            <span style="font-size: 26px; font-weight: bold; color: #ffffff; font-family: Arial, Helvetica, sans-serif;">CP</span>
        </td>
        </tr>
        </table>
        @endif
    </td>
    </tr>
    <tr>
    <td align="center" style="padding-top: 15px;">
        <span style="font-size: 22px; font-weight: bold; color: #1f2937; font-family: Arial, Helvetica, sans-serif;">{{ $systemName }}</span>
    </td>
    </tr>
    <tr>
    <td align="center" style="padding-top: 4px;">
        <span style="font-size: 13px; color: #6b7280; font-family: Arial, Helvetica, sans-serif;">Lembaga Sertifikasi Profesi</span>
    </td>
    </tr>
    </table>
</td>
</tr>

<!-- ===== BODY ===== -->
<tr>
<td style="padding: 35px 40px 30px 40px;">

    <!-- TITLE -->
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
    <tr>
    <td align="center" style="padding-bottom: 25px;">
        <span style="font-size: 24px; font-weight: bold; color: #111827; font-family: Arial, Helvetica, sans-serif;">Reset Password Anda</span>
    </td>
    </tr>
    </table>

    <!-- GREETING -->
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
    <tr>
    <td style="font-size: 15px; color: #374151; line-height: 24px; font-family: Arial, Helvetica, sans-serif;">
        Halo <strong>{{ $nama }}</strong>,
    </td>
    </tr>
    </table>

    <!-- MESSAGE -->
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
    <tr>
    <td style="padding-top: 18px; font-size: 15px; color: #374151; line-height: 24px; font-family: Arial, Helvetica, sans-serif;">
        Kami menerima permintaan untuk reset password akun Anda. Klik tombol di bawah ini untuk mengatur password baru.
    </td>
    </tr>
    </table>

    <!-- CTA BUTTON -->
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-top: 30px; margin-bottom: 30px;">
    <tr>
    <td align="center">
        <table role="presentation" cellpadding="0" cellspacing="0" border="0">
        <tr>
        <td align="center" bgcolor="#2563eb" style="background-color: #2563eb; border-radius: 6px; mso-padding-alt: 14px 40px;">
            <a href="{{ $resetLink }}" target="_blank" rel="noopener noreferrer" style="display: inline-block; padding: 14px 40px; font-size: 16px; font-weight: bold; color: #ffffff; text-decoration: none; font-family: Arial, Helvetica, sans-serif; border-radius: 6px;">Reset Password</a>
        </td>
        </tr>
        </table>
    </td>
    </tr>
    </table>

    <!-- EXPIRY INFO -->
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
    <tr>
    <td bgcolor="#fef3c7" style="background-color: #fef3c7; padding: 16px 20px; border-left: 4px solid #f59e0b; border-radius: 0 6px 6px 0;">
        <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
        <tr>
        <td style="font-size: 14px; font-weight: bold; color: #92400e; font-family: Arial, Helvetica, sans-serif;">
            Link Berlaku Terbatas
        </td>
        </tr>
        <tr>
        <td style="padding-top: 6px; font-size: 14px; color: #92400e; line-height: 22px; font-family: Arial, Helvetica, sans-serif;">
            Link ini hanya berlaku selama <strong>{{ $expiresInMinutes }} menit</strong> dan hanya dapat digunakan satu kali.
        </td>
        </tr>
        </table>
    </td>
    </tr>
    </table>

    <!-- PASSWORD REQUIREMENTS -->
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-top: 25px;">
    <tr>
    <td bgcolor="#f0fdf4" style="background-color: #f0fdf4; padding: 16px 20px; border-left: 4px solid #22c55e; border-radius: 0 6px 6px 0;">
        <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
        <tr>
        <td style="font-size: 14px; font-weight: bold; color: #166534; font-family: Arial, Helvetica, sans-serif;">
            Persyaratan Password Baru
        </td>
        </tr>
        <tr>
        <td style="padding-top: 10px; font-size: 13px; color: #166534; line-height: 22px; font-family: Arial, Helvetica, sans-serif;">
            &#8226; Minimal 8 karakter<br />
            &#8226; Kombinasi huruf besar dan kecil<br />
            &#8226; Minimal 1 angka
        </td>
        </tr>
        </table>
    </td>
    </tr>
    </table>

    <!-- ALTERNATIVE LINK -->
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-top: 25px;">
    <tr>
    <td style="font-size: 13px; color: #6b7280; line-height: 20px; font-family: Arial, Helvetica, sans-serif;">
        Jika tombol tidak berfungsi, salin dan tempel link berikut ke browser:
    </td>
    </tr>
    <tr>
    <td style="padding-top: 10px; font-size: 12px; color: #2563eb; line-height: 18px; word-break: break-all; font-family: Arial, Helvetica, sans-serif;">
        {{ $resetLink }}
    </td>
    </tr>
    </table>

    <!-- SECURITY NOTICE -->
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-top: 25px;">
    <tr>
    <td bgcolor="#dbeafe" style="background-color: #dbeafe; padding: 16px 20px; border-left: 4px solid #3b82f6; border-radius: 0 6px 6px 0;">
        <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
        <tr>
        <td style="font-size: 14px; font-weight: bold; color: #1e40af; font-family: Arial, Helvetica, sans-serif;">
            Bukan Anda?
        </td>
        </tr>
        <tr>
        <td style="padding-top: 6px; font-size: 14px; color: #1e40af; line-height: 22px; font-family: Arial, Helvetica, sans-serif;">
            Jika Anda tidak meminta reset password, abaikan email ini. Password Anda tidak akan berubah dan akun tetap aman.
        </td>
        </tr>
        </table>
    </td>
    </tr>
    </table>

</td>
</tr>

<!-- ===== FOOTER ===== -->
<tr>
<td style="padding: 25px 40px 30px 40px; background-color: #f9fafb; border-top: 1px solid #e5e7eb;">
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
    <tr>
    <td align="center" style="font-size: 15px; font-weight: bold; color: #374151; font-family: Arial, Helvetica, sans-serif;">
        {{ $systemName }}
    </td>
    </tr>
    <tr>
    <td align="center" style="padding-top: 4px; font-size: 13px; color: #6b7280; font-family: Arial, Helvetica, sans-serif;">
        Lembaga Sertifikasi Profesi
    </td>
    </tr>
    <tr>
    <td align="center" style="padding-top: 20px; font-size: 12px; color: #9ca3af; line-height: 20px; font-family: Arial, Helvetica, sans-serif;">
        Email ini dikirim secara otomatis oleh sistem. Jangan membalas email ini.<br />
        Jika ada pertanyaan, hubungi administrator sistem Anda.
    </td>
    </tr>
    <tr>
    <td align="center" style="padding-top: 18px; font-size: 12px; color: #9ca3af; font-family: Arial, Helvetica, sans-serif;">
        &copy; {{ $currentYear }} {{ $systemName }}. All rights reserved.
    </td>
    </tr>
    </table>
</td>
</tr>

</table>
<!-- END MAIN CONTAINER -->

</td>
</tr>
</table>
<!-- END WRAPPER -->

</body>
</html>
