{{--
================================================================================
CertiPro LSP - Email Template: Hasil Sertifikasi - BELUM KOMPETEN
================================================================================
File: resources/views/emails/belum-kompeten.blade.php
Subject: [LSP] Hasil Sertifikasi: BELUM KOMPETEN
Status Badge: WARNING (Orange/Yellow)
Variables: nama, nomor_pendaftaran
================================================================================
--}}

@extends('emails.layout')

@section('title', 'Hasil Sertifikasi: BELUM KOMPETEN')

@section('preheader')
Pemberitahuan hasil asesmen sertifikasi - Status: Belum Kompeten
@endsection

@section('status_badge')
<table role="presentation" cellpadding="0" cellspacing="0" border="0">
    <tr>
        <td style="background-color: #FFFBEB; border: 2px solid #F59E0B; border-radius: 50px; padding: 10px 24px;">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td style="padding-right: 8px; vertical-align: middle;">
                        <!-- Info Icon -->
                        <div style="width: 20px; height: 20px; background-color: #F59E0B; border-radius: 50%; text-align: center; line-height: 20px;">
                            <span style="color: #FFFFFF; font-size: 12px; font-weight: bold;">!</span>
                        </div>
                    </td>
                    <td style="vertical-align: middle;">
                        <span style="font-size: 14px; font-weight: bold; color: #D97706; text-transform: uppercase; letter-spacing: 0.5px;">
                            Belum Kompeten
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
    Terima kasih telah mengikuti proses asesmen sertifikasi{{ $systemName ? ' di ' . $systemName : '' }}.
    Setelah melalui evaluasi menyeluruh, kami sampaikan hasil asesmen Anda sebagai berikut:
</p>

<!-- Result Box -->
<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color: #FFFBEB; border-radius: 8px; border-left: 4px solid #F59E0B; margin-bottom: 24px;">
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
                    <td>
                        <span style="font-size: 12px; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">
                            Hasil Asesmen
                        </span>
                        <div style="margin-top: 4px;">
                            <span style="display: inline-block; background-color: #FEF3C7; color: #B45309; font-size: 14px; font-weight: bold; padding: 6px 16px; border-radius: 4px; text-transform: uppercase;">
                                Belum Kompeten
                            </span>
                        </div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<!-- Explanation -->
<h3 style="margin: 0 0 12px 0; font-size: 16px; font-weight: bold; color: #1E293B;">
    Apa Artinya Ini?
</h3>

<p style="margin: 0 0 20px 0; font-size: 14px; color: #475569; line-height: 1.7;">
    Status <strong>"Belum Kompeten"</strong> berarti terdapat beberapa aspek kompetensi yang perlu ditingkatkan.
    Hasil ini <strong>bukan berarti kegagalan permanen</strong>, melainkan kesempatan untuk mengembangkan
    diri lebih lanjut sesuai standar kompetensi yang ditetapkan.
</p>

<!-- What You Can Do -->
<h3 style="margin: 0 0 12px 0; font-size: 16px; font-weight: bold; color: #1E293B;">
    Hak dan Pilihan Anda:
</h3>

<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-bottom: 24px;">
    <!-- Option 1 -->
    <tr>
        <td style="padding: 16px 0; border-bottom: 1px solid #E2E8F0;">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                <tr>
                    <td style="width: 48px; vertical-align: top;">
                        <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #059669 0%, #10B981 100%); border-radius: 8px; text-align: center; line-height: 40px;">
                            <span style="color: #FFFFFF; font-size: 18px;">🔄</span>
                        </div>
                    </td>
                    <td style="padding-left: 16px; vertical-align: top;">
                        <p style="margin: 0 0 4px 0; font-size: 15px; font-weight: bold; color: #1E293B;">
                            Asesmen Ulang (Re-Assessment)
                        </p>
                        <p style="margin: 0; font-size: 13px; color: #64748B; line-height: 1.5;">
                            Anda berhak mengikuti asesmen ulang untuk unit kompetensi yang belum tercapai.
                            Asesmen ulang dapat dilakukan dalam waktu 30 hari kerja setelah hasil diumumkan.
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <!-- Option 2 -->
    <tr>
        <td style="padding: 16px 0; border-bottom: 1px solid #E2E8F0;">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                <tr>
                    <td style="width: 48px; vertical-align: top;">
                        <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #0D47A1 0%, #1565C0 100%); border-radius: 8px; text-align: center; line-height: 40px;">
                            <span style="color: #FFFFFF; font-size: 18px;">📚</span>
                        </div>
                    </td>
                    <td style="padding-left: 16px; vertical-align: top;">
                        <p style="margin: 0 0 4px 0; font-size: 15px; font-weight: bold; color: #1E293B;">
                            Persiapan Tambahan
                        </p>
                        <p style="margin: 0; font-size: 13px; color: #64748B; line-height: 1.5;">
                            Pelajari kembali materi kompetensi, ikuti pelatihan tambahan, atau konsultasi
                            dengan mentor untuk meningkatkan pemahaman dan keterampilan.
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <!-- Option 3 -->
    <tr>
        <td style="padding: 16px 0;">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                <tr>
                    <td style="width: 48px; vertical-align: top;">
                        <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #7C3AED 0%, #8B5CF6 100%); border-radius: 8px; text-align: center; line-height: 40px;">
                            <span style="color: #FFFFFF; font-size: 18px;">📞</span>
                        </div>
                    </td>
                    <td style="padding-left: 16px; vertical-align: top;">
                        <p style="margin: 0 0 4px 0; font-size: 15px; font-weight: bold; color: #1E293B;">
                            Konsultasi dengan Tim Kami
                        </p>
                        <p style="margin: 0; font-size: 13px; color: #64748B; line-height: 1.5;">
                            Hubungi tim kami untuk mendapatkan umpan balik detail tentang area yang perlu
                            ditingkatkan dan rekomendasi langkah persiapan.
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
                        <span style="font-size: 18px;">💡</span>
                    </td>
                    <td style="vertical-align: top;">
                        <p style="margin: 0; font-size: 14px; color: #1E40AF; line-height: 1.6;">
                            <strong>Catatan Penting:</strong> Banyak profesional sukses yang pernah mengalami
                            hasil serupa sebelum akhirnya berhasil. Keberhasilan ditentukan oleh kemauan
                            untuk terus belajar dan berkembang.
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<!-- Closing -->
<p style="margin: 0; font-size: 14px; color: #64748B; line-height: 1.6;">
    Kami tetap mendukung perjalanan profesional Anda dan siap membantu
    dalam proses pengembangan kompetensi selanjutnya.
</p>
@endsection

@section('cta')
<table role="presentation" cellpadding="0" cellspacing="0" border="0">
    <tr>
        <td style="padding-right: 8px;">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td style="background: linear-gradient(135deg, #0D47A1 0%, #1565C0 100%); border-radius: 8px;">
                        <a href="{{ config('app.url') }}/portal/asesmen-ulang" target="_blank" style="display: inline-block; padding: 14px 24px; font-size: 14px; font-weight: bold; color: #FFFFFF; text-decoration: none; text-align: center;">
                            Daftar Asesmen Ulang
                        </a>
                    </td>
                </tr>
            </table>
        </td>
        <td style="padding-left: 8px;">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td style="background-color: #FFFFFF; border: 2px solid #0D47A1; border-radius: 8px;">
                        <a href="{{ config('app.url') }}/kontak" target="_blank" style="display: inline-block; padding: 12px 24px; font-size: 14px; font-weight: bold; color: #0D47A1; text-decoration: none; text-align: center;">
                            Hubungi Kami
                        </a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
@endsection
