@extends('emails.layouts.master')

@section('content')
<!-- Main Content -->
<tr>
    <td style="padding: 40px;">
        <!-- Status Badge -->
        <div style="text-align: center; margin-bottom: 30px;">
            <span style="display: inline-block; background-color: #fef3c7; color: #92400e; padding: 8px 20px; border-radius: 20px; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                ⏳ Menunggu Verifikasi
            </span>
        </div>
        
        <!-- Greeting -->
        <h2 style="margin: 0 0 20px; color: #1e293b; font-size: 22px; font-weight: 600;">
            Yth. {{ $praPendaftaran->nama_lengkap }},
        </h2>
        
        <p style="margin: 0 0 20px; color: #475569; font-size: 15px;">
            Terima kasih telah melakukan pra-pendaftaran. 
            Data pendaftaran Anda telah kami terima dan sedang dalam proses verifikasi.
        </p>
        
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
                        <span style="color: #64748b; font-size: 13px;">Nama Lengkap</span>
                    </td>
                    <td style="padding: 10px 0; border-bottom: 1px solid #e2e8f0; text-align: right;">
                        <span style="color: #1e293b; font-size: 14px; font-weight: 500;">
                            {{ $praPendaftaran->nama_lengkap }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 10px 0; border-bottom: 1px solid #e2e8f0;">
                        <span style="color: #64748b; font-size: 13px;">Tipe Peserta</span>
                    </td>
                    <td style="padding: 10px 0; border-bottom: 1px solid #e2e8f0; text-align: right;">
                        <span style="color: #1e293b; font-size: 14px; font-weight: 500;">
                            {{ $praPendaftaran->tipe_peserta_label }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 10px 0;">
                        <span style="color: #64748b; font-size: 13px;">Tanggal Daftar</span>
                    </td>
                    <td style="padding: 10px 0; text-align: right;">
                        <span style="color: #1e293b; font-size: 14px; font-weight: 500;">
                            {{ $praPendaftaran->created_at->format('d F Y, H:i') }} WIB
                        </span>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Info Box -->
        <div style="background-color: #eff6ff; border-left: 4px solid #3b82f6; padding: 20px; margin: 25px 0; border-radius: 0 8px 8px 0;">
            <p style="margin: 0; color: #1e40af; font-size: 14px;">
                <strong>ℹ️ Informasi Penting:</strong><br>
                Proses verifikasi memerlukan waktu <strong>1-3 hari kerja</strong>. 
                Anda akan menerima email pemberitahuan setelah proses verifikasi selesai.
            </p>
        </div>
        
        <!-- CTA Button -->
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $statusUrl }}" 
               style="display: inline-block; background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%); color: #ffffff; text-decoration: none; padding: 14px 35px; border-radius: 8px; font-size: 15px; font-weight: 600; box-shadow: 0 4px 14px -3px rgba(15, 118, 110, 0.4);">
                📊 Cek Status Pendaftaran
            </a>
        </div>
        
        <p style="margin: 25px 0 0; color: #475569; font-size: 14px;">
            Simpan nomor pra-pendaftaran Anda dengan baik. Nomor ini akan digunakan untuk 
            memantau status dan melanjutkan ke tahap pendaftaran sertifikasi.
        </p>
        
        <!-- Signature -->
        <div style="margin-top: 35px; padding-top: 20px; border-top: 1px solid #e2e8f0;">
            <p style="margin: 0; color: #475569; font-size: 14px;">
                Hormat kami,<br><br>
                <strong style="color: #1e293b;">Tim Administrasi</strong><br>
                <span style="color: #64748b;">{{ $systemName ?? 'LSP' }}</span>
            </p>
        </div>
    </td>
</tr>
@endsection
