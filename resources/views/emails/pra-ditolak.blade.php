{{--
================================================================================
CertiPro LSP - Email Template: Pra-Pendaftaran Ditolak
================================================================================
File: resources/views/emails/pra-ditolak.blade.php
Subject: [LSP] Pra-Pendaftaran Ditolak – {{ nomor_pra_pendaftaran }}
Status Badge: DANGER (Red)
Variables: nama, nomor_pra_pendaftaran, alasan_penolakan
================================================================================
--}}

@extends('emails.layout')

@section('title', 'Pra-Pendaftaran Ditolak')

@section('preheader')
Pra-Pendaftaran Anda dengan nomor {{ $nomor_pra_pendaftaran }} tidak dapat diproses lebih lanjut.
@endsection

@section('status_badge')
<table role="presentation" cellpadding="0" cellspacing="0" border="0">
    <tr>
        <td style="background-color: #FEF2F2; border: 2px solid #EF4444; border-radius: 50px; padding: 10px 24px;">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td style="padding-right: 8px; vertical-align: middle;">
                        <!-- X Icon -->
                        <div style="width: 20px; height: 20px; background-color: #EF4444; border-radius: 50%; text-align: center; line-height: 20px;">
                            <span style="color: #FFFFFF; font-size: 12px; font-weight: bold;">✕</span>
                        </div>
                    </td>
                    <td style="vertical-align: middle;">
                        <span style="font-size: 14px; font-weight: bold; color: #DC2626; text-transform: uppercase; letter-spacing: 0.5px;">
                            Pra-Pendaftaran Ditolak
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
    Terima kasih atas minat Anda untuk melakukan sertifikasi profesi{{ $systemName ? ' di ' . $systemName : '' }}.
    Setelah dilakukan peninjauan, kami menyampaikan bahwa permohonan pra-pendaftaran Anda <strong style="color: #DC2626;">tidak dapat diproses lebih lanjut</strong>.
</p>

<!-- Info Box -->
<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color: #FEF2F2; border-radius: 8px; border-left: 4px solid #EF4444; margin-bottom: 24px;">
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
                    <td>
                        <span style="font-size: 12px; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">
                            Status Final
                        </span>
                        <div style="margin-top: 4px;">
                            <span style="display: inline-block; background-color: #FEE2E2; color: #DC2626; font-size: 12px; font-weight: bold; padding: 4px 12px; border-radius: 4px; text-transform: uppercase;">
                                Ditolak
                            </span>
                        </div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<!-- Reason Section -->
<h3 style="margin: 0 0 12px 0; font-size: 16px; font-weight: bold; color: #1E293B;">
    Alasan Penolakan:
</h3>

<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color: #FFFBEB; border-radius: 8px; border: 1px solid #FCD34D; margin-bottom: 24px;">
    <tr>
        <td style="padding: 20px;">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td style="width: 24px; vertical-align: top; padding-right: 12px;">
                        <span style="font-size: 18px;">⚠️</span>
                    </td>
                    <td style="vertical-align: top;">
                        <p style="margin: 0; font-size: 14px; color: #92400E; line-height: 1.6;">
                            {{ $alasan_penolakan }}
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<!-- What You Can Do -->
<h3 style="margin: 0 0 12px 0; font-size: 16px; font-weight: bold; color: #1E293B;">
    Yang Dapat Anda Lakukan:
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
                            <strong>Perbaiki kekurangan</strong> sesuai alasan penolakan di atas
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
                            <strong>Ajukan ulang</strong> pra-pendaftaran dengan dokumen yang lengkap
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
                            <strong>Hubungi kami</strong> jika memerlukan klarifikasi lebih lanjut
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<!-- Closing -->
<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color: #F1F5F9; border-radius: 8px; margin-bottom: 16px;">
    <tr>
        <td style="padding: 16px 20px;">
            <p style="margin: 0; font-size: 14px; color: #475569; line-height: 1.6;">
                <strong>Catatan:</strong> Keputusan ini bersifat final untuk pengajuan pra-pendaftaran saat ini.
                Anda tetap dapat mengajukan pendaftaran baru dengan memenuhi persyaratan yang ditentukan.
            </p>
        </td>
    </tr>
</table>

<p style="margin: 0; font-size: 14px; color: #64748B; line-height: 1.6;">
    Kami menghargai ketertarikan Anda dan berharap dapat melayani Anda di kesempatan berikutnya.
</p>
@endsection

@section('cta')
<table role="presentation" cellpadding="0" cellspacing="0" border="0">
    <tr>
        <td style="background: linear-gradient(135deg, #0D47A1 0%, #1565C0 100%); border-radius: 8px;">
            <a href="{{ config('app.url') }}/pendaftaran" target="_blank" style="display: inline-block; padding: 14px 32px; font-size: 15px; font-weight: bold; color: #FFFFFF; text-decoration: none; text-align: center;">
                Daftar Ulang →
            </a>
        </td>
    </tr>
</table>
@endsection
