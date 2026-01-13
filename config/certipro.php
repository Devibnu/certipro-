<?php

return [

    /*
    |--------------------------------------------------------------------------
    | CertiPro LSP Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi untuk Lembaga Sertifikasi Profesi CertiPro
    | Semua value bisa di-override via .env file
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Informasi LSP
    |--------------------------------------------------------------------------
    */
    
    'nama_lsp' => env('CERTIPRO_NAMA_LSP', 'LSP CertiPro'),
    'alamat' => env('CERTIPRO_ALAMAT', 'Jakarta, Indonesia'),
    'telepon' => env('CERTIPRO_TELEPON', '021-12345678'),
    'email' => env('CERTIPRO_EMAIL', 'info@certipro.id'),
    'website' => env('CERTIPRO_WEBSITE', 'https://certipro.id'),

    /*
    |--------------------------------------------------------------------------
    | Pejabat & Tanda Tangan
    |--------------------------------------------------------------------------
    */
    
    // Ketua LSP (untuk tanda tangan sertifikat)
    'ketua_lsp' => env('CERTIPRO_KETUA_LSP', 'Dr. Ahmad Hidayat, M.Kom'),
    
    // Kota tempat terbit sertifikat
    'kota_terbit' => env('CERTIPRO_KOTA_TERBIT', 'Jakarta'),
    
    /*
    |--------------------------------------------------------------------------
    | Lisensi BNSP
    |--------------------------------------------------------------------------
    */
    
    // Nomor Lisensi BNSP
    'nomor_lisensi' => env('CERTIPRO_NOMOR_LISENSI', 'LSP-XXXXX-ID'),
    
    /*
    |--------------------------------------------------------------------------
    | Sertifikat Settings
    |--------------------------------------------------------------------------
    */
    
    // Masa Berlaku Sertifikat (dalam tahun)
    'masa_berlaku_sertifikat' => env('CERTIPRO_MASA_BERLAKU', 3),

    // Format Nomor Sertifikat
    // Available placeholders: {PREFIX}, {YEAR}, {NUMBER}
    'format_nomor_sertifikat' => env('CERTIPRO_FORMAT_NOMOR', 'LSP-CP/{YEAR}/{NUMBER}'),
    'prefix_sertifikat' => env('CERTIPRO_PREFIX', 'LSP-CP'),

    /*
    |--------------------------------------------------------------------------
    | Logo & Branding
    |--------------------------------------------------------------------------
    | Path relatif terhadap folder public
    */
    
    'logo_lsp' => env('CERTIPRO_LOGO_LSP', 'images/logo-lsp.png'),
    'logo_bnsp' => env('CERTIPRO_LOGO_BNSP', 'images/logo-bnsp.png'),

    /*
    |--------------------------------------------------------------------------
    | PDF Settings
    |--------------------------------------------------------------------------
    */
    
    'pdf' => [
        'paper_size' => 'A4',
        'orientation' => 'portrait',
        'dpi' => 150,
    ],

];
