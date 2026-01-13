<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php $systemName = systemCompanyName(); @endphp
    <title>Email {{ $systemName ?? 'LSP' }}</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7fa; line-height: 1.6;">
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #f4f7fa;">
        <tr>
            <td style="padding: 40px 20px;">
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="600" style="margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                    
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%); padding: 30px 40px; text-align: center;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td>
                                        <h1 style="margin: 0; color: #ffffff; font-size: 28px; font-weight: 700; letter-spacing: -0.5px;">
                                            {{ $systemName ?? 'LSP' }}
                                        </h1>
                                        <p style="margin: 8px 0 0; color: rgba(255, 255, 255, 0.9); font-size: 14px;">
                                            Lembaga Sertifikasi Profesi Terakreditasi BNSP
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    @yield('content')
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8fafc; padding: 30px 40px; border-top: 1px solid #e2e8f0;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td style="text-align: center;">
                                        <p style="margin: 0 0 15px; color: #64748b; font-size: 13px;">
                                            <strong>{{ $systemName ?? 'LSP' }}</strong><br>
                                            Jl. Contoh Alamat No. 123, Jakarta<br>
                                            Telp: (021) 123-4567 | Email: info@lsp.id
                                        </p>
                                        
                                        <div style="margin: 20px 0; padding: 15px; background-color: #f0fdf4; border-radius: 8px; border: 1px solid #bbf7d0;">
                                            <p style="margin: 0; color: #166534; font-size: 11px; line-height: 1.5;">
                                                <strong>🛡️ Kepatuhan & Sertifikasi</strong><br>
                                                Lembaga ini beroperasi sesuai dengan standar BNSP (Badan Nasional Sertifikasi Profesi) 
                                                dan memenuhi persyaratan ISO 17024:2012 untuk lembaga sertifikasi personel.
                                            </p>
                                        </div>
                                        
                                        <p style="margin: 15px 0 0; color: #94a3b8; font-size: 11px;">
                                            Email ini dikirim secara otomatis oleh sistem. Mohon tidak membalas email ini.<br>
                                            &copy; {{ date('Y') }} {{ $systemName ?? 'LSP' }}. Hak cipta dilindungi undang-undang.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
