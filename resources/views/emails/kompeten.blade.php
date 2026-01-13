{{--
================================================================================
CertiPro LSP - Email Template: Hasil Sertifikasi - KOMPETEN
================================================================================
File: resources/views/emails/kompeten.blade.php
Subject: [LSP] Hasil Sertifikasi: KOMPETEN
Status Badge: SUCCESS (Green)
Variables: nama, nomor_sertifikat, masa_berlaku, link_sertifikat
================================================================================
--}}

@extends('emails.layout')

@section('title', 'Hasil Sertifikasi: KOMPETEN')

@section('preheader')
Selamat! Anda telah dinyatakan KOMPETEN dan berhak menerima sertifikat kompetensi.
@endsection

@section('status_badge')
<table role="presentation" cellpadding="0" cellspacing="0" border="0">
    <tr>
        <td style="background-color: #ECFDF5; border: 2px solid #10B981; border-radius: 50px; padding: 10px 24px;">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td style="padding-right: 8px; vertical-align: middle;">
                        <!-- Trophy Icon -->
                        <div style="width: 24px; height: 24px; text-align: center; line-height: 24px;">
                            <span style="font-size: 18px;">🏆</span>
                        </div>
                    </td>
                    <td style="vertical-align: middle;">
                        <span style="font-size: 16px; font-weight: bold; color: #059669; text-transform: uppercase; letter-spacing: 1px;">
                            KOMPETEN
                        </span>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
@endsection

@section('reference_number')
{{ $nomor_sertifikat }}
@endsection

@section('content')
<!-- Greeting -->
<h2 style="margin: 0 0 16px 0; font-size: 20px; font-weight: bold; color: #1E293B;">
    Yth. {{ $nama }},
</h2>

<!-- Congratulations Message -->
<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background: linear-gradient(135deg, #ECFDF5 0%, #D1FAE5 100%); border-radius: 12px; margin-bottom: 24px;">
    <tr>
        <td style="padding: 24px; text-align: center;">
            <div style="font-size: 48px; margin-bottom: 12px;">🎉</div>
            <h3 style="margin: 0 0 8px 0; font-size: 22px; font-weight: bold; color: #065F46;">
                Selamat!
            </h3>
            <p style="margin: 0; font-size: 15px; color: #047857; line-height: 1.6;">
                Berdasarkan hasil asesmen yang telah dilaksanakan, Anda dinyatakan<br>
                <strong style="font-size: 18px; color: #059669;">KOMPETEN</strong><br>
                dan berhak menerima Sertifikat Kompetensi
            </p>
        </td>
    </tr>
</table>

<!-- Certificate Info -->
<h3 style="margin: 0 0 16px 0; font-size: 16px; font-weight: bold; color: #1E293B;">
    Informasi Sertifikat:
</h3>

<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color: #F8FAFC; border-radius: 8px; border: 1px solid #E2E8F0; margin-bottom: 24px;">
    <tr>
        <td style="padding: 20px;">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                <!-- Certificate Number -->
                <tr>
                    <td style="padding-bottom: 16px; border-bottom: 1px dashed #CBD5E1;">
                        <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                            <tr>
                                <td style="width: 40px; vertical-align: top;">
                                    <div style="width: 32px; height: 32px; background-color: #0D47A1; border-radius: 6px; text-align: center; line-height: 32px;">
                                        <span style="color: #FFFFFF; font-size: 14px;">📜</span>
                                    </div>
                                </td>
                                <td style="padding-left: 12px; vertical-align: top;">
                                    <span style="font-size: 12px; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">
                                        Nomor Sertifikat
                                    </span>
                                    <div style="font-size: 16px; font-weight: bold; color: #0D47A1; font-family: 'Courier New', monospace; margin-top: 2px;">
                                        {{ $nomor_sertifikat }}
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <!-- Validity Period -->
                <tr>
                    <td style="padding-top: 16px;">
                        <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                            <tr>
                                <td style="width: 40px; vertical-align: top;">
                                    <div style="width: 32px; height: 32px; background-color: #059669; border-radius: 6px; text-align: center; line-height: 32px;">
                                        <span style="color: #FFFFFF; font-size: 14px;">📅</span>
                                    </div>
                                </td>
                                <td style="padding-left: 12px; vertical-align: top;">
                                    <span style="font-size: 12px; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">
                                        Masa Berlaku
                                    </span>
                                    <div style="font-size: 16px; font-weight: bold; color: #1E293B; margin-top: 2px;">
                                        {{ $masa_berlaku }}
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<!-- What's Next -->
<h3 style="margin: 0 0 12px 0; font-size: 16px; font-weight: bold; color: #1E293B;">
    Langkah Selanjutnya:
</h3>

<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-bottom: 24px;">
    <tr>
        <td style="padding: 12px 0; border-bottom: 1px solid #E2E8F0;">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td style="width: 28px; vertical-align: top;">
                        <div style="width: 24px; height: 24px; background-color: #10B981; border-radius: 50%; text-align: center; line-height: 24px;">
                            <span style="color: #FFFFFF; font-size: 12px; font-weight: bold;">✓</span>
                        </div>
                    </td>
                    <td style="padding-left: 12px; vertical-align: top;">
                        <p style="margin: 0; font-size: 14px; color: #475569; line-height: 1.5;">
                            <strong>Unduh sertifikat digital</strong> melalui tombol di bawah ini
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
                        <div style="width: 24px; height: 24px; background-color: #10B981; border-radius: 50%; text-align: center; line-height: 24px;">
                            <span style="color: #FFFFFF; font-size: 12px; font-weight: bold;">✓</span>
                        </div>
                    </td>
                    <td style="padding-left: 12px; vertical-align: top;">
                        <p style="margin: 0; font-size: 14px; color: #475569; line-height: 1.5;">
                            <strong>Sertifikat fisik</strong> akan dikirim ke alamat terdaftar (7-14 hari kerja)
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
                        <div style="width: 24px; height: 24px; background-color: #10B981; border-radius: 50%; text-align: center; line-height: 24px;">
                            <span style="color: #FFFFFF; font-size: 12px; font-weight: bold;">✓</span>
                        </div>
                    </td>
                    <td style="padding-left: 12px; vertical-align: top;">
                        <p style="margin: 0; font-size: 14px; color: #475569; line-height: 1.5;">
                            <strong>Verifikasi online</strong> tersedia di situs resmi BNSP
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<!-- Reminder Note -->
<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color: #FFFBEB; border-radius: 8px; border: 1px solid #FCD34D; margin-bottom: 24px;">
    <tr>
        <td style="padding: 16px 20px;">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td style="width: 24px; vertical-align: top; padding-right: 12px;">
                        <span style="font-size: 18px;">⏰</span>
                    </td>
                    <td style="vertical-align: top;">
                        <p style="margin: 0; font-size: 14px; color: #92400E; line-height: 1.6;">
                            <strong>Pengingat:</strong> Sertifikat Anda berlaku selama 3 tahun.
                            Pastikan untuk melakukan <strong>sertifikasi ulang</strong> sebelum masa berlaku habis
                            untuk menjaga status kompetensi Anda.
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<p style="margin: 0; font-size: 14px; color: #64748B; line-height: 1.6;">
    Terima kasih telah mempercayakan sertifikasi kompetensi Anda kepada kami.
    Semoga sertifikat ini dapat mendukung karir profesional Anda.
</p>
@endsection

@section('cta')
<table role="presentation" cellpadding="0" cellspacing="0" border="0">
    <tr>
        <td style="background: linear-gradient(135deg, #059669 0%, #10B981 100%); border-radius: 8px;">
            <a href="{{ $link_sertifikat }}" target="_blank" style="display: inline-block; padding: 14px 32px; font-size: 15px; font-weight: bold; color: #FFFFFF; text-decoration: none; text-align: center;">
                📥 Unduh Sertifikat
            </a>
        </td>
    </tr>
</table>
<p style="margin: 12px 0 0 0; font-size: 12px; color: #94A3B8;">
    Atau salin link: <a href="{{ $link_sertifikat }}" style="color: #0D47A1;">{{ $link_sertifikat }}</a>
</p>
@endsection
