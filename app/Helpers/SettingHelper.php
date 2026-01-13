<?php

use App\Models\LogoAdmin;
use App\Models\SystemSetting;

if (!function_exists('systemLogo')) {
    /**
     * Get system logo path for PDF rendering
     * 
     * @param string $type 'admin' atau 'public'
     * @return string Absolute path ke file logo
     */
    function systemLogo(string $type = 'public'): string
    {
        // Ambil logo aktif dari database
        $logo = LogoAdmin::active()->first();
        
        if ($logo && $logo->gambar) {
            $storagePath = storage_path('app/public/' . $logo->gambar);
            
            // Pastikan file exists
            if (file_exists($storagePath)) {
                return $storagePath;
            }
        }
        
        // Fallback ke logo default
        $defaultPath = public_path('images/logo-default.png');
        
        if (file_exists($defaultPath)) {
            return $defaultPath;
        }
        
        // Fallback terakhir - gunakan placeholder
        return public_path('images/logo-placeholder.png');
    }
}

if (!function_exists('systemLogoBase64')) {
    /**
     * Get system logo as base64 for inline PDF embedding
     * Lebih reliable untuk DomPDF
     * 
     * @param string $type 'admin' atau 'public'
     * @return string Base64 encoded image dengan data URI
     */
    function systemLogoBase64(string $type = 'public'): string
    {
        $path = systemLogo($type);
        
        if (!file_exists($path)) {
            return '';
        }
        
        $imageData = file_get_contents($path);
        $mimeType = mime_content_type($path);
        
        return 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
    }
}

if (!function_exists('systemLogoUrl')) {
    /**
     * Get system logo URL for web display (HTML img src)
     * 
     * @return string|null URL ke logo atau null jika tidak ada
     */
    function systemLogoUrl(): ?string
    {
        $logo = \App\Models\LogoAdmin::active()->first();
        
        if ($logo && $logo->gambar) {
            // Return asset URL untuk storage
            return asset('storage/' . $logo->gambar);
        }
        
        return null;
    }
}

if (!function_exists('systemLogoInfo')) {
    /**
     * Get complete logo info for audit purposes
     * 
     * @return array
     */
    function systemLogoInfo(): array
    {
        $logo = LogoAdmin::active()->first();
        
        if (!$logo) {
            return [
                'source' => 'default',
                'path' => 'images/logo-default.png',
                'version' => null,
                'updated_at' => null,
                'nama_perusahaan' => null,
                'tagline' => null,
            ];
        }
        
        // Return nama_perusahaan as-is (null if empty)
        $namaPerusahaan = $logo->nama_perusahaan;
        if ($namaPerusahaan !== null && trim($namaPerusahaan) === '') {
            $namaPerusahaan = null;
        }
        
        return [
            'source' => 'database',
            'path' => $logo->gambar,
            'version' => $logo->id,
            'updated_at' => $logo->updated_at?->toIso8601String(),
            'nama_perusahaan' => $namaPerusahaan,
            'tagline' => $logo->tagline,
        ];
    }
}

if (!function_exists('setting')) {
    /**
     * Get system setting value
     * 
     * @param string $key Format: "group.key" atau hanya "key"
     * @param mixed $default
     * @return mixed
     */
    function setting(string $key, mixed $default = null): mixed
    {
        // Parse group.key format
        if (str_contains($key, '.')) {
            [$group, $settingKey] = explode('.', $key, 2);
        } else {
            $group = 'general';
            $settingKey = $key;
        }
        
        return SystemSetting::getValue($group, $settingKey, $default);
    }
}

if (!function_exists('setSetting')) {
    /**
     * Set system setting value
     * 
     * @param string $key Format: "group.key" atau hanya "key"
     * @param mixed $value
     * @param bool $isEncrypted
     * @return \App\Models\SystemSetting
     */
    function setSetting(string $key, mixed $value, bool $isEncrypted = false): SystemSetting
    {
        // Parse group.key format
        if (str_contains($key, '.')) {
            [$group, $settingKey] = explode('.', $key, 2);
        } else {
            $group = 'general';
            $settingKey = $key;
        }
        
        return SystemSetting::setValue($group, $settingKey, $value, $isEncrypted);
    }
}

if (!function_exists('systemCompanyName')) {
    /**
     * Get system company name for branding
     * Returns NULL if not set - NO FALLBACK
     * 
     * @return string|null
     */
    function systemCompanyName(): ?string
    {
        $logo = \App\Models\LogoAdmin::active()->first();
        
        if ($logo && $logo->nama_perusahaan && trim($logo->nama_perusahaan) !== '') {
            return $logo->nama_perusahaan;
        }
        
        // Return NULL - no hardcoded fallback
        return null;
    }
}

if (!function_exists('systemTagline')) {
    /**
     * Get system tagline for branding
     * 
     * @return string|null
     */
    function systemTagline(): ?string
    {
        $logo = \App\Models\LogoAdmin::active()->first();
        
        return $logo?->tagline;
    }
}
