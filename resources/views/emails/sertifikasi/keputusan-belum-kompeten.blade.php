@extends('emails.layouts.master')

@section('title', 'Hasil Asesmen – Belum Kompeten')

@section('content')
    <!-- Header Icon -->
    <tr>
        <td style="text-align: center; padding: 30px 40px 20px;">
            <div style="width: 80px; height: 80px; margin: 0 auto; background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                <span style="font-size: 40px; color: white;">📋</span>
            </div>
        </td>
    </tr>

    <!-- Main Content -->
    <tr>
        <td style="padding: 0 40px 30px;">
            <h2 style="color: #1a202c; font-size: 24px; font-weight: 600; margin: 0 0 20px; text-align: center;">
                Hasil Asesmen Sertifikasi
            </h2>

            <p style="color: #4a5568; font-size: 16px; line-height: 1.6; margin: 0 0 20px;">
                Yth. <strong>{{ $pendaftaran->nama_lengkap }}</strong>,
            </p>

            <p style="color: #4a5568; font-size: 16px; line-height: 1.6; margin: 0 0 20px;">
                Kami informasikan bahwa berdasarkan hasil asesmen yang telah dilaksanakan, Anda dinyatakan <strong style="color: #ef4444;">BELUM KOMPETEN</strong> pada skema sertifikasi yang diikuti.
            </p>

            <!-- Detail Box -->
            <div style="background: #fef2f2; border-left: 4px solid #ef4444; padding: 20px; margin: 30px 0; border-radius: 4px;">
                <p style="color: #991b1b; font-size: 14px; font-weight: 600; margin: 0 0 15px; text-transform: uppercase; letter-spacing: 0.5px;">
                    📋 DETAIL HASIL ASESMEN:
                </p>
                
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 8px 0; color: #b91c1c; font-size: 14px; width: 40%;">
                            <strong>Nomor Pendaftaran:</strong>
                        </td>
                        <td style="padding: 8px 0; color: #991b1b; font-size: 14px;">
                            {{ $pendaftaran->nomor_pendaftaran }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #b91c1c; font-size: 14px;">
                            <strong>Skema Sertifikasi:</strong>
                        </td>
                        <td style="padding: 8px 0; color: #991b1b; font-size: 14px;">
                            <strong>{{ $skema->kode_skema ?? 'N/A' }}</strong><br>
                            <span style="font-size: 13px;">{{ $skema->nama_skema ?? 'N/A' }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #b91c1c; font-size: 14px;">
                            <strong>Tanggal Asesmen:</strong>
                        </td>
                        <td style="padding: 8px 0; color: #991b1b; font-size: 14px;">
                            {{ $asesmen->tanggal_asesmen ? \Carbon\Carbon::parse($asesmen->tanggal_asesmen)->format('d F Y') : 'N/A' }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #b91c1c; font-size: 14px;">
                            <strong>Keputusan:</strong>
                        </td>
                        <td style="padding: 8px 0; color: #991b1b; font-size: 14px;">
                            <span style="background: #ef4444; color: white; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">
                                BELUM KOMPETEN
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #b91c1c; font-size: 14px;">
                            <strong>Tanggal Keputusan:</strong>
                        </td>
                        <td style="padding: 8px 0; color: #991b1b; font-size: 14px;">
                            {{ $asesmen->updated_at->format('d F Y, H:i') }} WIB
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Catatan Asesor -->
            @if(!empty($asesmen->catatan_asesor))
            <div style="background: #fffbeb; border-left: 4px solid #f59e0b; padding: 20px; margin: 30px 0; border-radius: 4px;">
                <p style="color: #92400e; font-size: 14px; font-weight: 600; margin: 0 0 10px;">
                    📝 CATATAN ASESOR:
                </p>
                <p style="color: #78350f; font-size: 14px; margin: 0; line-height: 1.6;">
                    {{ $asesmen->catatan_asesor }}
                </p>
            </div>
            @endif

            <!-- Next Steps -->
            <div style="background: #eff6ff; border-left: 4px solid #3b82f6; padding: 20px; margin: 30px 0; border-radius: 4px;">
                <p style="color: #1e40af; font-size: 14px; font-weight: 600; margin: 0 0 10px;">
                    💡 LANGKAH SELANJUTNYA:
                </p>
                <ol style="color: #1e3a8a; font-size: 14px; margin: 0; padding-left: 20px; line-height: 1.8;">
                    <li>Review catatan dan feedback dari asesor</li>
                    <li>Identifikasi area yang perlu diperbaiki/ditingkatkan</li>
                    <li>Tingkatkan kompetensi melalui pelatihan atau praktik tambahan</li>
                    <li>Anda dapat <strong>mengajukan asesmen ulang</strong> setelah mempersiapkan diri lebih baik</li>
                    <li>Hubungi admin LSP untuk informasi jadwal asesmen ulang</li>
                </ol>
            </div>

            <!-- Encouragement -->
            <div style="background: #f0fdf4; border-left: 4px solid #10b981; padding: 20px; margin: 30px 0; border-radius: 4px;">
                <p style="color: #065f46; font-size: 14px; margin: 0; line-height: 1.6;">
                    💪 <strong>Jangan Berkecil Hati!</strong> Hasil ini adalah bagian dari proses pembelajaran. Dengan persiapan yang lebih matang, Anda dapat meraih hasil yang lebih baik pada asesmen berikutnya.
                </p>
            </div>

            <p style="color: #4a5568; font-size: 14px; line-height: 1.6; margin: 20px 0 0;">
                Kami tetap mendukung perjalanan sertifikasi Anda. Jangan ragu untuk menghubungi admin LSP jika membutuhkan bantuan atau informasi lebih lanjut.
            </p>
        </td>
    </tr>
@endsection

@section('footer-note')
    <p style="color: #718096; font-size: 12px; margin: 0;">
        Email ini dikirim otomatis oleh sistem. <strong>Jangan balas</strong> email ini.
        <br>
        Untuk pertanyaan atau konsultasi, silakan hubungi admin LSP.
    </p>
@endsection
