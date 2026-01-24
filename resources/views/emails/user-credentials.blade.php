@extends('emails.layouts.master')

@section('title', 'Akun Anda di ' . $systemName)

@section('content')
    <!-- Header with Icon -->
    <tr>
        <td style="text-align: center; padding: 30px 40px 20px;">
            <div style="width: 80px; height: 80px; margin: 0 auto; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                <span style="font-size: 40px; color: white;">🔑</span>
            </div>
        </td>
    </tr>

    <!-- Main Content -->
    <tr>
        <td style="padding: 0 40px 30px;">
            <h2 style="color: #1a202c; font-size: 24px; font-weight: 600; margin: 0 0 20px; text-align: center;">
                Akun Anda Telah Dibuat!
            </h2>

            <p style="color: #4a5568; font-size: 16px; line-height: 1.6; margin: 0 0 20px;">
                Yth. <strong>{{ $user->name }}</strong>,
            </p>

            <p style="color: #4a5568; font-size: 16px; line-height: 1.6; margin: 0 0 20px;">
                Akun Anda di <strong>{{ $systemName }}</strong> telah berhasil dibuat untuk pendaftaran sertifikasi <strong>{{ $pendaftaran->nomor_pendaftaran }}</strong>.
            </p>

            <!-- Credentials Box -->
            <div style="background: #f7fafc; border-left: 4px solid #667eea; padding: 20px; margin: 30px 0; border-radius: 4px;">
                <p style="color: #2d3748; font-size: 14px; font-weight: 600; margin: 0 0 15px; text-transform: uppercase; letter-spacing: 0.5px;">
                    📋 Kredensial Login Anda:
                </p>
                
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 8px 0; color: #718096; font-size: 14px; width: 30%;">
                            <strong>Username/Email:</strong>
                        </td>
                        <td style="padding: 8px 0; color: #2d3748; font-size: 14px; font-family: 'Courier New', monospace;">
                            <strong>{{ $user->email }}</strong>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #718096; font-size: 14px;">
                            <strong>Password:</strong>
                        </td>
                        <td style="padding: 8px 0; color: #2d3748; font-size: 14px; font-family: 'Courier New', monospace;">
                            <strong>{{ $temporaryPassword }}</strong>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Warning Box -->
            <div style="background: #fffbeb; border-left: 4px solid #f59e0b; padding: 15px; margin: 20px 0; border-radius: 4px;">
                <p style="color: #92400e; font-size: 14px; margin: 0; line-height: 1.5;">
                    ⚠️ <strong>Penting:</strong> Segera ubah password Anda setelah login pertama kali untuk keamanan akun Anda.
                </p>
            </div>

            <!-- Login Button -->
            <table style="width: 100%; margin: 30px 0;">
                <tr>
                    <td style="text-align: center;">
                        <a href="{{ $loginUrl }}" 
                           style="display: inline-block; padding: 14px 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px; box-shadow: 0 4px 6px rgba(102, 126, 234, 0.3);">
                            🚀 Login Sekarang
                        </a>
                    </td>
                </tr>
            </table>

            <!-- Next Steps -->
            <div style="background: #f0fdf4; border-left: 4px solid #10b981; padding: 20px; margin: 30px 0; border-radius: 4px;">
                <p style="color: #065f46; font-size: 14px; font-weight: 600; margin: 0 0 10px;">
                    📌 Langkah Selanjutnya:
                </p>
                <ol style="color: #047857; font-size: 14px; margin: 0; padding-left: 20px; line-height: 1.8;">
                    <li>Login ke sistem menggunakan kredensial di atas</li>
                    <li>Ubah password Anda di menu Profil</li>
                    <li>Lengkapi data pendaftaran sertifikasi Anda</li>
                    <li>Pilih skema sertifikasi yang diinginkan</li>
                    <li>Upload dokumen yang diperlukan</li>
                </ol>
            </div>

            <p style="color: #4a5568; font-size: 14px; line-height: 1.6; margin: 20px 0 0;">
                Jika Anda mengalami kesulitan saat login, silakan hubungi tim kami.
            </p>
        </td>
    </tr>
@endsection

@section('footer-note')
    <p style="color: #718096; font-size: 12px; margin: 0;">
        Email ini dikirim otomatis oleh sistem. <strong>Jangan balas</strong> email ini.
        <br>
        Jika Anda tidak merasa mendaftar di {{ $systemName }}, abaikan email ini.
    </p>
@endsection
