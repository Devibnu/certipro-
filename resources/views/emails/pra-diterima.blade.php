{{--
================================================================================
CertiPro LSP - Email Template: Pra-Pendaftaran Diterima
================================================================================
File: resources/views/emails/pra-diterima.blade.php
Subject: [LSP] Pra-Pendaftaran Diterima – {{ nomor_pra_pendaftaran }}
Status Badge: SUCCESS (Green)
Variables: nama, nomor_pra_pendaftaran, tanggal, link_status
================================================================================
--}}

@extends('emails.layout')

@section('title', 'Pra-Pendaftaran Diterima')

@section('preheader')
Pra-Pendaftaran Anda dengan nomor {{ $nomor_pra_pendaftaran }} telah diterima dan menunggu verifikasi lanjutan.
@endsection

@section('status_badge')
<table role="presentation" cellpadding="0" cellspacing="0" border="0">
    <tr>
        <td style="background-color: #ECFDF5; border: 2px solid #10B981; border-radius: 50px; padding: 10px 24px;">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td style="padding-right: 8px; vertical-align: middle;">
                        <!-- Checkmark Icon -->
                        <div style="width: 20px; height: 20px; background-color: #10B981; border-radius: 50%; text-align: center; line-height: 20px;">
                            <span style="color: #FFFFFF; font-size: 12px; font-weight: bold;">✓</span>
                        </div>
                    </td>
                    <td style="vertical-align: middle;">
                        <span style="font-size: 14px; font-weight: bold; color: #059669; text-transform: uppercase; letter-spacing: 0.5px;">
                            Pra-Pendaftaran Diterima
                        </span>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
@endsection

@section('reference_number')
{{ $nomor_pra_pendaftaran }}
@endsection

@section('content')
<!-- Greeting -->
<h2 style="margin: 0 0 16px 0; font-size: 20px; font-weight: bold; color: #1E293B;">
    Yth. {{ $nama }},
</h2>

@php $systemName = systemCompanyName(); @endphp
<p style="margin: 0 0 20px 0; font-size: 15px; color: #475569; line-height: 1.7;">
    Terima kasih telah melakukan pra-pendaftaran sertifikasi profesi{{ $systemName ? ' di ' . $systemName : '' }}.
    Kami dengan senang hati menginformasikan bahwa permohonan pra-pendaftaran Anda telah <strong style="color: #059669;">berhasil diterima</strong>.
</p>

<!-- Info Box -->
<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color: #F0FDF4; border-radius: 8px; border-left: 4px solid #22C55E; margin-bottom: 24px;">
    <tr>
        <td style="padding: 20px;">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                <tr>
                    <td style="padding-bottom: 12px;">
                        <span style="font-size: 12px; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">
                            Nomor Pra-Pendaftaran
                        </span>
                        <div style="font-size: 18px; font-weight: bold; color: #0D47A1; font-family: 'Courier New', monospace; margin-top: 4px;">
                            {{ $nomor_pra_pendaftaran }}
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="padding-bottom: 12px;">
                        <span style="font-size: 12px; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">
                            Tanggal Pendaftaran
                        </span>
                        <div style="font-size: 15px; font-weight: 600; color: #1E293B; margin-top: 4px;">
                            {{ $tanggal }}
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <span style="font-size: 12px; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">
                            Status Saat Ini
                        </span>
                        <div style="margin-top: 4px;">
                            <span style="display: inline-block; background-color: #FEF3C7; color: #B45309; font-size: 12px; font-weight: bold; padding: 4px 12px; border-radius: 4px; text-transform: uppercase;">
                                Menunggu Verifikasi Lanjutan
                            </span>
                        </div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<!-- Next Steps -->
<h3 style="margin: 0 0 12px 0; font-size: 16px; font-weight: bold; color: #1E293B;">
    Langkah Selanjutnya:
</h3>

<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-bottom: 24px;">
    <tr>
        <td style="padding: 12px 0; border-bottom: 1px solid #E2E8F0;">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td style="width: 28px; vertical-align: top;">
                        <div style="width: 24px; height: 24px; background-color: #0D47A1; border-radius: 50%; text-align: center; line-height: 24px;">
                            <span style="color: #FFFFFF; font-size: 12px; font-weight: bold;">1</span>
                        </div>
                    </td>
                    <td style="padding-left: 12px; vertical-align: top;">
                        <p style="margin: 0; font-size: 14px; color: #475569; line-height: 1.5;">
                            Tim kami akan melakukan <strong>verifikasi data</strong> yang Anda kirimkan
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td style="padding: 12px 0; border-bottom: 1px solid #E2E8F0;">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td style="width: 28px; vertical-align: top;">
                        <div style="width: 24px; height: 24px; background-color: #0D47A1; border-radius: 50%; text-align: center; line-height: 24px;">
                            <span style="color: #FFFFFF; font-size: 12px; font-weight: bold;">2</span>
                        </div>
                    </td>
                    <td style="padding-left: 12px; vertical-align: top;">
                        <p style="margin: 0; font-size: 14px; color: #475569; line-height: 1.5;">
                            Anda akan menerima <strong>notifikasi email</strong> setelah proses verifikasi selesai
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td style="padding: 12px 0;">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td style="width: 28px; vertical-align: top;">
                        <div style="width: 24px; height: 24px; background-color: #0D47A1; border-radius: 50%; text-align: center; line-height: 24px;">
                            <span style="color: #FFFFFF; font-size: 12px; font-weight: bold;">3</span>
                        </div>
                    </td>
                    <td style="padding-left: 12px; vertical-align: top;">
                        <p style="margin: 0; font-size: 14px; color: #475569; line-height: 1.5;">
                            Pantau status pendaftaran Anda melalui <strong>portal peserta</strong>
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<p style="margin: 0; font-size: 14px; color: #64748B; line-height: 1.6;">
    Jika Anda memiliki pertanyaan, silakan hubungi tim kami melalui halaman kontak atau balas email ini.
</p>
@endsection

@section('cta')
<table role="presentation" cellpadding="0" cellspacing="0" border="0">
    <tr>
        <td style="background: linear-gradient(135deg, #0D47A1 0%, #1565C0 100%); border-radius: 8px;">
            <a href="{{ $link_status }}" target="_blank" style="display: inline-block; padding: 14px 32px; font-size: 15px; font-weight: bold; color: #FFFFFF; text-decoration: none; text-align: center;">
                Cek Status Pendaftaran →
            </a>
        </td>
    </tr>
</table>
<p style="margin: 12px 0 0 0; font-size: 12px; color: #94A3B8;">
    Atau salin link: <a href="{{ $link_status }}" style="color: #0D47A1;">{{ $link_status }}</a>
</p>
@endsection
