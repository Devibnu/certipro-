@extends('emails.layouts.master')

@section('title', 'Skema Sertifikasi Ditetapkan')

@section('content')
    <!-- Header Icon -->
    <tr>
        <td style="text-align: center; padding: 30px 40px 20px;">
            <div style="width: 80px; height: 80px; margin: 0 auto; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                <span style="font-size: 40px; color: white;">✓</span>
            </div>
        </td>
    </tr>

    <!-- Main Content -->
    <tr>
        <td style="padding: 0 40px 30px;">
            <h2 style="color: #1a202c; font-size: 24px; font-weight: 600; margin: 0 0 20px; text-align: center;">
                Skema Sertifikasi Ditetapkan
            </h2>

            <p style="color: #4a5568; font-size: 16px; line-height: 1.6; margin: 0 0 20px;">
                Yth. <strong>{{ $pendaftaran->nama_lengkap }}</strong>,
            </p>

            <p style="color: #4a5568; font-size: 16px; line-height: 1.6; margin: 0 0 20px;">
                Kami informasikan bahwa skema sertifikasi Anda telah ditetapkan dan pendaftaran Anda kini berstatus <strong style="color: #10b981;">SIAP ASESMEN</strong>.
            </p>

            <!-- Detail Box -->
            <div style="background: #f0fdf4; border-left: 4px solid #10b981; padding: 20px; margin: 30px 0; border-radius: 4px;">
                <p style="color: #065f46; font-size: 14px; font-weight: 600; margin: 0 0 15px; text-transform: uppercase; letter-spacing: 0.5px;">
                    📋 DETAIL PENDAFTARAN:
                </p>
                
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 8px 0; color: #047857; font-size: 14px; width: 40%;">
                            <strong>Nomor Pendaftaran:</strong>
                        </td>
                        <td style="padding: 8px 0; color: #065f46; font-size: 14px;">
                            {{ $pendaftaran->nomor_pendaftaran }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #047857; font-size: 14px;">
                            <strong>Skema Sertifikasi:</strong>
                        </td>
                        <td style="padding: 8px 0; color: #065f46; font-size: 14px;">
                            <strong>{{ $skema->kode_skema ?? 'N/A' }}</strong><br>
                            <span style="font-size: 13px;">{{ $skema->nama_skema ?? 'N/A' }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #047857; font-size: 14px;">
                            <strong>Status:</strong>
                        </td>
                        <td style="padding: 8px 0; color: #065f46; font-size: 14px;">
                            <span style="background: #10b981; color: white; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">
                                SIAP ASESMEN
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #047857; font-size: 14px;">
                            <strong>Tanggal Ditetapkan:</strong>
                        </td>
                        <td style="padding: 8px 0; color: #065f46; font-size: 14px;">
                            {{ $pendaftaran->updated_at->format('d F Y, H:i') }} WIB
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
                    <li>Siapkan dokumen pendukung (portofolio, sertifikat, bukti pengalaman kerja)</li>
                    <li>Tunggu jadwal asesmen dari tim LSP</li>
                    <li>Anda akan menerima notifikasi email saat asesmen dijadwalkan</li>
                    <li>Pastikan datang tepat waktu dan siap untuk asesmen</li>
                </ol>
            </div>

            <!-- Info Box -->
            <div style="background: #eff6ff; border-left: 4px solid #3b82f6; padding: 20px; margin: 30px 0; border-radius: 4px;">
                <p style="color: #1e40af; font-size: 14px; margin: 0; line-height: 1.6;">
                    ℹ️ <strong>Informasi:</strong> Asesmen akan dilaksanakan sesuai dengan metode yang tercantum dalam skema sertifikasi (observasi langsung, wawancara, portofolio, atau kombinasi).
                </p>
            </div>

            <p style="color: #4a5568; font-size: 14px; line-height: 1.6; margin: 20px 0 0;">
                Terima kasih atas partisipasi Anda dalam proses sertifikasi kompetensi.
            </p>
        </td>
    </tr>
@endsection

@section('footer-note')
    <p style="color: #718096; font-size: 12px; margin: 0;">
        Email ini dikirim otomatis oleh sistem. <strong>Jangan balas</strong> email ini.
        <br>
        Untuk pertanyaan, silakan hubungi admin LSP.
    </p>
@endsection
