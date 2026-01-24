@extends('emails.layouts.master')

@section('title', 'Asesmen Telah Selesai')

@section('content')
    <!-- Header Icon -->
    <tr>
        <td style="text-align: center; padding: 30px 40px 20px;">
            <div style="width: 80px; height: 80px; margin: 0 auto; background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                <span style="font-size: 40px; color: white;">📝</span>
            </div>
        </td>
    </tr>

    <!-- Main Content -->
    <tr>
        <td style="padding: 0 40px 30px;">
            <h2 style="color: #1a202c; font-size: 24px; font-weight: 600; margin: 0 0 20px; text-align: center;">
                Asesmen Telah Selesai
            </h2>

            <p style="color: #4a5568; font-size: 16px; line-height: 1.6; margin: 0 0 20px;">
                Yth. <strong>{{ $pendaftaran->nama_lengkap }}</strong>,
            </p>

            <p style="color: #4a5568; font-size: 16px; line-height: 1.6; margin: 0 0 20px;">
                Kami informasikan bahwa proses asesmen Anda telah <strong>selesai dilaksanakan</strong>. Saat ini hasil asesmen Anda dalam status <strong style="color: #f59e0b;">MENUNGGU KEPUTUSAN</strong> dari Komite Teknis.
            </p>

            <!-- Detail Box -->
            <div style="background: #eff6ff; border-left: 4px solid #3b82f6; padding: 20px; margin: 30px 0; border-radius: 4px;">
                <p style="color: #1e3a8a; font-size: 14px; font-weight: 600; margin: 0 0 15px; text-transform: uppercase; letter-spacing: 0.5px;">
                    📋 DETAIL ASESMEN:
                </p>
                
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 8px 0; color: #1e40af; font-size: 14px; width: 40%;">
                            <strong>Nomor Pendaftaran:</strong>
                        </td>
                        <td style="padding: 8px 0; color: #1e3a8a; font-size: 14px;">
                            {{ $pendaftaran->nomor_pendaftaran }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #1e40af; font-size: 14px;">
                            <strong>Skema Sertifikasi:</strong>
                        </td>
                        <td style="padding: 8px 0; color: #1e3a8a; font-size: 14px;">
                            <strong>{{ $skema->kode_skema ?? 'N/A' }}</strong><br>
                            <span style="font-size: 13px;">{{ $skema->nama_skema ?? 'N/A' }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #1e40af; font-size: 14px;">
                            <strong>Metode Asesmen:</strong>
                        </td>
                        <td style="padding: 8px 0; color: #1e3a8a; font-size: 14px;">
                            {{ $asesmen->metode_asesmen ?? 'Observasi & Wawancara' }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #1e40af; font-size: 14px;">
                            <strong>Tanggal Pelaksanaan:</strong>
                        </td>
                        <td style="padding: 8px 0; color: #1e3a8a; font-size: 14px;">
                            {{ $asesmen->tanggal_asesmen ? \Carbon\Carbon::parse($asesmen->tanggal_asesmen)->format('d F Y') : 'N/A' }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #1e40af; font-size: 14px;">
                            <strong>Status:</strong>
                        </td>
                        <td style="padding: 8px 0; color: #1e3a8a; font-size: 14px;">
                            <span style="background: #f59e0b; color: white; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">
                                MENUNGGU KEPUTUSAN
                            </span>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Info Timeline -->
            <div style="background: #fffbeb; border-left: 4px solid #f59e0b; padding: 20px; margin: 30px 0; border-radius: 4px;">
                <p style="color: #92400e; font-size: 14px; font-weight: 600; margin: 0 0 10px;">
                    ⏳ PROSES SELANJUTNYA:
                </p>
                <ol style="color: #78350f; font-size: 14px; margin: 0; padding-left: 20px; line-height: 1.8;">
                    <li>Hasil asesmen akan direview oleh Asesor</li>
                    <li>Komite Teknis akan melakukan validasi hasil</li>
                    <li>Keputusan akhir (Kompeten/Belum Kompeten) akan ditetapkan</li>
                    <li>Anda akan menerima <strong>email notifikasi otomatis</strong> setelah keputusan ditetapkan</li>
                </ol>
            </div>

            <!-- Estimate Time -->
            <div style="background: #f0fdf4; border-left: 4px solid #10b981; padding: 20px; margin: 30px 0; border-radius: 4px;">
                <p style="color: #065f46; font-size: 14px; margin: 0; line-height: 1.6;">
                    ℹ️ <strong>Estimasi Waktu:</strong> Proses evaluasi dan penetapan keputusan membutuhkan waktu 3-7 hari kerja. Anda akan segera menerima email notifikasi begitu keputusan ditetapkan.
                </p>
            </div>

            <p style="color: #4a5568; font-size: 14px; line-height: 1.6; margin: 20px 0 0;">
                Terima kasih atas partisipasi Anda dalam proses asesmen. Kami berharap yang terbaik untuk hasil asesmen Anda.
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
