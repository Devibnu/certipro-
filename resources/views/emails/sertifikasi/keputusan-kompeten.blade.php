@extends('emails.layouts.master')

@section('title', 'Selamat! Anda Dinyatakan KOMPETEN')

@section('content')
    <!-- Header Icon -->
    <tr>
        <td style="text-align: center; padding: 30px 40px 20px;">
            <div style="width: 100px; height: 100px; margin: 0 auto; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 10px 30px rgba(16, 185, 129, 0.3);">
                <span style="font-size: 50px; color: white;">🎉</span>
            </div>
        </td>
    </tr>

    <!-- Main Content -->
    <tr>
        <td style="padding: 0 40px 30px;">
            <h2 style="color: #10b981; font-size: 28px; font-weight: 700; margin: 0 0 10px; text-align: center;">
                SELAMAT!
            </h2>
            
            <h3 style="color: #1a202c; font-size: 20px; font-weight: 600; margin: 0 0 30px; text-align: center;">
                Anda Dinyatakan KOMPETEN
            </h3>

            <p style="color: #4a5568; font-size: 16px; line-height: 1.6; margin: 0 0 20px;">
                Yth. <strong>{{ $pendaftaran->nama_lengkap }}</strong>,
            </p>

            <p style="color: #4a5568; font-size: 16px; line-height: 1.6; margin: 0 0 20px;">
                Dengan bangga kami informasikan bahwa Anda telah <strong style="color: #10b981;">DINYATAKAN KOMPETEN</strong> dalam skema sertifikasi yang Anda ikuti. Selamat atas pencapaian luar biasa ini!
            </p>

            <!-- Detail Box -->
            <div style="background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%); border-left: 4px solid #10b981; padding: 25px; margin: 30px 0; border-radius: 8px; box-shadow: 0 4px 6px rgba(16, 185, 129, 0.1);">
                <p style="color: #065f46; font-size: 14px; font-weight: 600; margin: 0 0 15px; text-transform: uppercase; letter-spacing: 0.5px;">
                    🏆 DETAIL HASIL ASESMEN:
                </p>
                
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 10px 0; color: #047857; font-size: 14px; width: 40%;">
                            <strong>Nomor Pendaftaran:</strong>
                        </td>
                        <td style="padding: 10px 0; color: #065f46; font-size: 14px;">
                            {{ $pendaftaran->nomor_pendaftaran }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 0; color: #047857; font-size: 14px;">
                            <strong>Skema Sertifikasi:</strong>
                        </td>
                        <td style="padding: 10px 0; color: #065f46; font-size: 14px;">
                            <strong>{{ $skema->kode_skema ?? 'N/A' }}</strong><br>
                            <span style="font-size: 13px;">{{ $skema->nama_skema ?? 'N/A' }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 0; color: #047857; font-size: 14px;">
                            <strong>Tanggal Asesmen:</strong>
                        </td>
                        <td style="padding: 10px 0; color: #065f46; font-size: 14px;">
                            {{ $asesmen->tanggal_asesmen ? \Carbon\Carbon::parse($asesmen->tanggal_asesmen)->format('d F Y') : 'N/A' }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 0; color: #047857; font-size: 14px;">
                            <strong>Keputusan:</strong>
                        </td>
                        <td style="padding: 10px 0; color: #065f46; font-size: 14px;">
                            <span style="background: #10b981; color: white; padding: 6px 16px; border-radius: 20px; font-size: 13px; font-weight: 700; letter-spacing: 0.5px;">
                                ✓ KOMPETEN
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 0; color: #047857; font-size: 14px;">
                            <strong>Tanggal Keputusan:</strong>
                        </td>
                        <td style="padding: 10px 0; color: #065f46; font-size: 14px;">
                            {{ $asesmen->updated_at->format('d F Y, H:i') }} WIB
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Next Steps -->
            <div style="background: #fffbeb; border-left: 4px solid #f59e0b; padding: 20px; margin: 30px 0; border-radius: 4px;">
                <p style="color: #92400e; font-size: 14px; font-weight: 600; margin: 0 0 10px;">
                    📌 LANGKAH SELANJUTNYA:
                </p>
                <ol style="color: #78350f; font-size: 14px; margin: 0; padding-left: 20px; line-height: 1.8;">
                    <li><strong>Sertifikat kompetensi Anda akan segera diterbitkan</strong></li>
                    <li>Anda akan menerima email notifikasi saat sertifikat siap diunduh</li>
                    <li>Sertifikat dapat diakses melalui dashboard Anda di sistem LSP</li>
                    <li>Sertifikat berlaku sesuai dengan periode yang tercantum</li>
                </ol>
            </div>

            <!-- Congratulations Message -->
            <div style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); padding: 20px; margin: 30px 0; border-radius: 8px; text-align: center; border: 2px solid #f59e0b;">
                <p style="color: #92400e; font-size: 16px; font-weight: 600; margin: 0; line-height: 1.6;">
                    🌟 Selamat atas pencapaian Anda! 🌟<br>
                    <span style="font-size: 14px; font-weight: normal;">Kompeten menjadi standar, prestasi membuktikannya.</span>
                </p>
            </div>

            <p style="color: #4a5568; font-size: 14px; line-height: 1.6; margin: 20px 0 0;">
                Terima kasih atas dedikasi dan komitmen Anda dalam proses sertifikasi ini. Kami berharap sertifikat ini dapat mendukung pengembangan karier profesional Anda.
            </p>
        </td>
    </tr>
@endsection

@section('footer-note')
    <p style="color: #718096; font-size: 12px; margin: 0;">
        Email ini dikirim otomatis oleh sistem. <strong>Jangan balas</strong> email ini.
        <br>
        Untuk pertanyaan tentang sertifikat, silakan hubungi admin LSP.
    </p>
@endsection
