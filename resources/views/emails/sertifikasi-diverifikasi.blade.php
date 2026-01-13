{{--
================================================================================
CertiPro LSP - Email Template: Pendaftaran Sertifikasi Diverifikasi
================================================================================
File: resources/views/emails/sertifikasi-diverifikasi.blade.php
Subject: [LSP] Pendaftaran Sertifikasi Diverifikasi
Status Badge: SUCCESS (Green)
Variables: nama, nomor_pendaftaran, skema
================================================================================
--}}

@extends('emails.layout')

@section('title', 'Pendaftaran Sertifikasi Diverifikasi')

@section('preheader')
Pendaftaran sertifikasi Anda telah diverifikasi dan siap untuk tahap asesmen.
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
                            Verifikasi Berhasil
                        </span>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
@endsection

@section('reference_number')
{{ $nomor_pendaftaran }}
@endsection

@section('content')
<!-- Greeting -->
<h2 style="margin: 0 0 16px 0; font-size: 20px; font-weight: bold; color: #1E293B;">
    Yth. {{ $nama }},
</h2>

@php $systemName = systemCompanyName(); @endphp
<p style="margin: 0 0 20px 0; font-size: 15px; color: #475569; line-height: 1.7;">
    Selamat! Pendaftaran sertifikasi profesi Anda{{ $systemName ? ' di ' . $systemName : '' }} telah 
    <strong style="color: #059669;">berhasil diverifikasi</strong>. Anda kini memasuki tahap persiapan asesmen.
</p>

<!-- Info Box -->
<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color: #F0FDF4; border-radius: 8px; border-left: 4px solid #22C55E; margin-bottom: 24px;">
    <tr>
        <td style="padding: 20px;">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                <tr>
                    <td style="padding-bottom: 12px;">
                        <span style="font-size: 12px; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">
                            Nomor Pendaftaran
                        </span>
                        <div style="font-size: 18px; font-weight: bold; color: #0D47A1; font-family: 'Courier New', monospace; margin-top: 4px;">
                            {{ $nomor_pendaftaran }}
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="padding-bottom: 12px;">
                        <span style="font-size: 12px; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">
                            Skema Sertifikasi
                        </span>
                        <div style="font-size: 15px; font-weight: 600; color: #1E293B; margin-top: 4px;">
                            {{ $skema }}
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <span style="font-size: 12px; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">
                            Status Saat Ini
                        </span>
                        <div style="margin-top: 4px;">
                            <span style="display: inline-block; background-color: #DBEAFE; color: #1D4ED8; font-size: 12px; font-weight: bold; padding: 4px 12px; border-radius: 4px; text-transform: uppercase;">
                                Siap Asesmen
                            </span>
                        </div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<!-- Assessment Stages -->
<h3 style="margin: 0 0 12px 0; font-size: 16px; font-weight: bold; color: #1E293B;">
    Tahapan Asesmen yang Akan Dilalui:
</h3>

<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-bottom: 24px;">
    <!-- Stage 1 -->
    <tr>
        <td style="padding: 16px 0; border-bottom: 1px solid #E2E8F0;">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                <tr>
                    <td style="width: 48px; vertical-align: top;">
                        <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #0D47A1 0%, #1565C0 100%); border-radius: 8px; text-align: center; line-height: 40px;">
                            <span style="color: #FFFFFF; font-size: 16px; font-weight: bold;">1</span>
                        </div>
                    </td>
                    <td style="padding-left: 16px; vertical-align: top;">
                        <p style="margin: 0 0 4px 0; font-size: 15px; font-weight: bold; color: #1E293B;">
                            Asesmen Mandiri (Self Assessment)
                        </p>
                        <p style="margin: 0; font-size: 13px; color: #64748B; line-height: 1.5;">
                            Pengisian formulir asesmen mandiri untuk mengevaluasi kesiapan kompetensi Anda
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <!-- Stage 2 -->
    <tr>
        <td style="padding: 16px 0; border-bottom: 1px solid #E2E8F0;">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                <tr>
                    <td style="width: 48px; vertical-align: top;">
                        <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #0D47A1 0%, #1565C0 100%); border-radius: 8px; text-align: center; line-height: 40px;">
                            <span style="color: #FFFFFF; font-size: 16px; font-weight: bold;">2</span>
                        </div>
                    </td>
                    <td style="padding-left: 16px; vertical-align: top;">
                        <p style="margin: 0 0 4px 0; font-size: 15px; font-weight: bold; color: #1E293B;">
                            Uji Tertulis
                        </p>
                        <p style="margin: 0; font-size: 13px; color: #64748B; line-height: 1.5;">
                            Tes pengetahuan dan pemahaman teori sesuai skema sertifikasi
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <!-- Stage 3 -->
    <tr>
        <td style="padding: 16px 0; border-bottom: 1px solid #E2E8F0;">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                <tr>
                    <td style="width: 48px; vertical-align: top;">
                        <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #0D47A1 0%, #1565C0 100%); border-radius: 8px; text-align: center; line-height: 40px;">
                            <span style="color: #FFFFFF; font-size: 16px; font-weight: bold;">3</span>
                        </div>
                    </td>
                    <td style="padding-left: 16px; vertical-align: top;">
                        <p style="margin: 0 0 4px 0; font-size: 15px; font-weight: bold; color: #1E293B;">
                            Uji Praktik / Demonstrasi
                        </p>
                        <p style="margin: 0; font-size: 13px; color: #64748B; line-height: 1.5;">
                            Demonstrasi kemampuan praktis di hadapan asesor
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <!-- Stage 4 -->
    <tr>
        <td style="padding: 16px 0;">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                <tr>
                    <td style="width: 48px; vertical-align: top;">
                        <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #0D47A1 0%, #1565C0 100%); border-radius: 8px; text-align: center; line-height: 40px;">
                            <span style="color: #FFFFFF; font-size: 16px; font-weight: bold;">4</span>
                        </div>
                    </td>
                    <td style="padding-left: 16px; vertical-align: top;">
                        <p style="margin: 0 0 4px 0; font-size: 15px; font-weight: bold; color: #1E293B;">
                            Wawancara
                        </p>
                        <p style="margin: 0; font-size: 13px; color: #64748B; line-height: 1.5;">
                            Sesi tanya jawab untuk verifikasi dan klarifikasi kompetensi
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<!-- Important Note -->
<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color: #EFF6FF; border-radius: 8px; border: 1px solid #BFDBFE; margin-bottom: 24px;">
    <tr>
        <td style="padding: 16px 20px;">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td style="width: 24px; vertical-align: top; padding-right: 12px;">
                        <span style="font-size: 18px;">📋</span>
                    </td>
                    <td style="vertical-align: top;">
                        <p style="margin: 0; font-size: 14px; color: #1E40AF; line-height: 1.6;">
                            <strong>Penting:</strong> Jadwal asesmen akan diinformasikan melalui email terpisah.
                            Pastikan Anda mempersiapkan diri dengan mempelajari materi sesuai skema sertifikasi.
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<p style="margin: 0; font-size: 14px; color: #64748B; line-height: 1.6;">
    Jika memiliki pertanyaan terkait persiapan asesmen, silakan hubungi tim kami.
</p>
@endsection

@section('cta')
<table role="presentation" cellpadding="0" cellspacing="0" border="0">
    <tr>
        <td style="background: linear-gradient(135deg, #0D47A1 0%, #1565C0 100%); border-radius: 8px;">
            <a href="{{ config('app.url') }}/portal/asesmen" target="_blank" style="display: inline-block; padding: 14px 32px; font-size: 15px; font-weight: bold; color: #FFFFFF; text-decoration: none; text-align: center;">
                Lihat Detail Asesmen →
            </a>
        </td>
    </tr>
</table>
@endsection
