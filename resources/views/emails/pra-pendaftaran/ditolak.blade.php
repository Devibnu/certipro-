@extends('emails.layouts.master')

@section('content')
<!-- Main Content -->
<tr>
    <td style="padding: 40px;">
        <!-- Status Badge -->
        <div style="text-align: center; margin-bottom: 30px;">
            <span style="display: inline-block; background-color: #fef2f2; color: #dc2626; padding: 8px 20px; border-radius: 20px; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                ❌ Tidak Dapat Diproses
            </span>
        </div>
        
        <!-- Greeting -->
        <h2 style="margin: 0 0 20px; color: #1e293b; font-size: 22px; font-weight: 600;">
            Yth. {{ $praPendaftaran->nama_lengkap }},
        </h2>
        
        <p style="margin: 0 0 20px; color: #475569; font-size: 15px;">
            Terima kasih atas minat Anda untuk mengikuti program sertifikasi.
            Setelah melakukan verifikasi data, dengan berat hati kami informasikan bahwa 
            pra-pendaftaran Anda <strong>tidak dapat kami proses lebih lanjut</strong>.
        </p>
        
        <!-- Registration Details Box -->
        <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 25px; margin: 25px 0;">
            <h3 style="margin: 0 0 15px; color: #64748b; font-size: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                📋 Detail Pra-Pendaftaran
            </h3>
            
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                <tr>
                    <td style="padding: 10px 0; border-bottom: 1px solid #e2e8f0;">
                        <span style="color: #64748b; font-size: 13px;">Nomor Pra-Pendaftaran</span>
                    </td>
                    <td style="padding: 10px 0; border-bottom: 1px solid #e2e8f0; text-align: right;">
                        <span style="color: #64748b; font-size: 15px; font-weight: 700; font-family: 'Courier New', monospace; letter-spacing: 1px;">
                            {{ $praPendaftaran->nomor_pra_pendaftaran }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 10px 0; border-bottom: 1px solid #e2e8f0;">
                        <span style="color: #64748b; font-size: 13px;">Status</span>
                    </td>
                    <td style="padding: 10px 0; border-bottom: 1px solid #e2e8f0; text-align: right;">
                        <span style="display: inline-block; background-color: #fef2f2; color: #dc2626; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">
                            DITOLAK
                        </span>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 10px 0;">
                        <span style="color: #64748b; font-size: 13px;">Tanggal Keputusan</span>
                    </td>
                    <td style="padding: 10px 0; text-align: right;">
                        <span style="color: #1e293b; font-size: 14px; font-weight: 500;">
                            {{ $praPendaftaran->status_updated_at ? $praPendaftaran->status_updated_at->format('d F Y, H:i') : now()->format('d F Y, H:i') }} WIB
                        </span>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Rejection Reason Box -->
        @if($praPendaftaran->alasan_penolakan)
        <div style="background-color: #fef2f2; border: 1px solid #fecaca; border-left: 4px solid #ef4444; border-radius: 0 12px 12px 0; padding: 25px; margin: 25px 0;">
            <h3 style="margin: 0 0 12px; color: #dc2626; font-size: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                ⚠️ Alasan Penolakan
            </h3>
            <div style="background-color: #ffffff; border: 1px solid #fecaca; border-radius: 8px; padding: 15px;">
                <p style="margin: 0; color: #7f1d1d; font-size: 14px; line-height: 1.6;">
                    {{ $praPendaftaran->alasan_penolakan }}
                </p>
            </div>
        </div>
        @endif
        
        <!-- Info Box -->
        <div style="background-color: #eff6ff; border-left: 4px solid #3b82f6; padding: 20px; margin: 25px 0; border-radius: 0 8px 8px 0;">
            <p style="margin: 0; color: #1e40af; font-size: 14px;">
                <strong>ℹ️ Apa yang dapat Anda lakukan?</strong><br>
                Jika Anda memiliki pertanyaan atau ingin mengajukan klarifikasi, silakan hubungi 
                tim administrasi kami melalui halaman kontak atau datang langsung ke kantor kami 
                pada jam operasional.
            </p>
        </div>
        
        <!-- CTA Buttons -->
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $kontakUrl }}" 
               style="display: inline-block; background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%); color: #ffffff; text-decoration: none; padding: 14px 35px; border-radius: 8px; font-size: 15px; font-weight: 600; box-shadow: 0 4px 14px -3px rgba(15, 118, 110, 0.4); margin-bottom: 10px;">
                📞 Hubungi Kami
            </a>
        </div>
        
        <div style="text-align: center; margin-bottom: 20px;">
            <a href="{{ $statusUrl }}" 
               style="display: inline-block; background-color: #f1f5f9; color: #475569; text-decoration: none; padding: 12px 25px; border-radius: 8px; font-size: 14px; font-weight: 500; border: 1px solid #e2e8f0;">
                📊 Lihat Detail Status
            </a>
        </div>
        
        <!-- Disclaimer -->
        <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px; margin: 25px 0;">
            <p style="margin: 0; color: #64748b; font-size: 12px; line-height: 1.6;">
                <strong>📌 Catatan:</strong> Penolakan pra-pendaftaran ini hanya berkaitan dengan 
                kelengkapan atau kesesuaian data administrasi, <em>bukan</em> merupakan penilaian 
                terhadap kompetensi Anda. Anda dapat mendaftar kembali setelah memenuhi persyaratan 
                yang diperlukan.
            </p>
        </div>
        
        <!-- Signature -->
        <div style="margin-top: 35px; padding-top: 20px; border-top: 1px solid #e2e8f0;">
            <p style="margin: 0; color: #475569; font-size: 14px;">
                Hormat kami,<br><br>
                <strong style="color: #1e293b;">Tim Verifikasi</strong><br>
                <span style="color: #64748b;">{{ $systemName ?? 'LSP' }}</span>
            </p>
        </div>
    </td>
</tr>
@endsection
