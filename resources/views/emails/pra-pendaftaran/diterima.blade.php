@extends('emails.layouts.master')

@section('content')
<!-- Main Content -->
<tr>
    <td style="padding: 40px;">
        <!-- Status Badge -->
        <div style="text-align: center; margin-bottom: 30px;">
            <span style="display: inline-block; background-color: #dcfce7; color: #166534; padding: 8px 20px; border-radius: 20px; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                ✅ Diterima
            </span>
        </div>
        
        <!-- Greeting -->
        <h2 style="margin: 0 0 20px; color: #1e293b; font-size: 22px; font-weight: 600;">
            Selamat, {{ $praPendaftaran->nama_lengkap }}!
        </h2>
        
        <p style="margin: 0 0 20px; color: #475569; font-size: 15px;">
            Dengan ini kami informasikan bahwa <strong>pra-pendaftaran</strong> Anda 
            telah <strong style="color: #059669;">diverifikasi dan diterima</strong>.
        </p>
        
        <!-- Success Box -->
        <div style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border: 1px solid #86efac; border-radius: 12px; padding: 25px; margin: 25px 0; text-align: center;">
            <div style="font-size: 48px; margin-bottom: 15px;">🎉</div>
            <h3 style="margin: 0 0 10px; color: #166534; font-size: 18px; font-weight: 600;">
                Pra-Pendaftaran Berhasil Diverifikasi
            </h3>
            <p style="margin: 0; color: #15803d; font-size: 14px;">
                Anda dapat melanjutkan ke tahap Pendaftaran Sertifikasi
            </p>
        </div>
        
        <!-- Registration Details Box -->
        <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 25px; margin: 25px 0;">
            <h3 style="margin: 0 0 15px; color: #0f766e; font-size: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                📋 Detail Pra-Pendaftaran
            </h3>
            
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                <tr>
                    <td style="padding: 10px 0; border-bottom: 1px solid #e2e8f0;">
                        <span style="color: #64748b; font-size: 13px;">Nomor Pra-Pendaftaran</span>
                    </td>
                    <td style="padding: 10px 0; border-bottom: 1px solid #e2e8f0; text-align: right;">
                        <span style="color: #0f766e; font-size: 15px; font-weight: 700; font-family: 'Courier New', monospace; letter-spacing: 1px;">
                            {{ $praPendaftaran->nomor_pra_pendaftaran }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 10px 0; border-bottom: 1px solid #e2e8f0;">
                        <span style="color: #64748b; font-size: 13px;">Status</span>
                    </td>
                    <td style="padding: 10px 0; border-bottom: 1px solid #e2e8f0; text-align: right;">
                        <span style="display: inline-block; background-color: #dcfce7; color: #166534; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">
                            DITERIMA
                        </span>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 10px 0;">
                        <span style="color: #64748b; font-size: 13px;">Tanggal Verifikasi</span>
                    </td>
                    <td style="padding: 10px 0; text-align: right;">
                        <span style="color: #1e293b; font-size: 14px; font-weight: 500;">
                            {{ $praPendaftaran->status_updated_at ? $praPendaftaran->status_updated_at->format('d F Y, H:i') : now()->format('d F Y, H:i') }} WIB
                        </span>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Next Steps -->
        <div style="background-color: #fefce8; border-left: 4px solid #eab308; padding: 20px; margin: 25px 0; border-radius: 0 8px 8px 0;">
            <p style="margin: 0; color: #854d0e; font-size: 14px;">
                <strong>📌 Langkah Selanjutnya:</strong><br>
                Silakan lanjutkan ke tahap <strong>Pendaftaran Sertifikasi</strong> untuk:
            </p>
            <ul style="margin: 10px 0 0; padding-left: 20px; color: #854d0e; font-size: 14px;">
                <li>Memilih skema kompetensi yang diinginkan</li>
                <li>Melengkapi dokumen persyaratan</li>
                <li>Memilih jadwal asesmen yang tersedia</li>
            </ul>
        </div>
        
        <!-- CTA Buttons -->
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $daftarUrl }}" 
               style="display: inline-block; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #ffffff; text-decoration: none; padding: 14px 35px; border-radius: 8px; font-size: 15px; font-weight: 600; box-shadow: 0 4px 14px -3px rgba(16, 185, 129, 0.4); margin-bottom: 10px;">
                🚀 Lanjut Daftar Sertifikasi
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
                <strong>⚠️ Penting:</strong> Email ini merupakan konfirmasi penerimaan pra-pendaftaran, 
                <em>bukan</em> keputusan sertifikasi. Keputusan kompetensi akan ditentukan setelah Anda 
                mengikuti proses asesmen sesuai dengan skema yang dipilih.
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
