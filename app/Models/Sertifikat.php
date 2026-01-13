<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Sertifikat extends Model
{
    use Auditable;

    /**
     * The table associated with the model.
     */
    protected $table = 'sertifikat';

    /**
     * Get the audit module name.
     */
    public function getAuditModule(): string
    {
        return 'sertifikat';
    }

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'pendaftaran_id',
        'nomor_sertifikat',
        'uuid',
        'nama_peserta',
        'skema_sertifikasi',
        'tanggal_terbit',
        'tanggal_berlaku_sampai',
        'qr_code',
        'file_pdf',
        'diterbitkan_oleh',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'tanggal_terbit' => 'date',
        'tanggal_berlaku_sampai' => 'date',
        'pendaftaran_id' => 'integer',
        'diterbitkan_oleh' => 'integer',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-generate UUID when creating
        static::creating(function ($sertifikat) {
            if (empty($sertifikat->uuid)) {
                $sertifikat->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Generate security hash for verification.
     * Hash = sha256(uuid + nomor_sertifikat)
     */
    public function getSecurityHashAttribute(): string
    {
        return hash('sha256', $this->uuid . $this->nomor_sertifikat);
    }

    /**
     * Get the pendaftaran that owns the sertifikat.
     */
    public function pendaftaran(): BelongsTo
    {
        return $this->belongsTo(PendaftaranSertifikasi::class, 'pendaftaran_id');
    }

    /**
     * Get the user (admin) who issued the certificate.
     */
    public function penerbit(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diterbitkan_oleh');
    }

    /**
     * Generate nomor sertifikat unique.
     * Format: CERT/CTP/{TAHUN}/{RUNNING_NUMBER}
     * Example: CERT/CTP/2026/000123
     */
    public static function generateNomorSertifikat(): string
    {
        $prefix = 'CERT/CTP';
        $year = date('Y');
        
        // Get last number for this year
        $lastRecord = self::where('nomor_sertifikat', 'like', "{$prefix}/{$year}/%")
            ->orderBy('id', 'desc')
            ->first();
        
        if ($lastRecord) {
            // Extract the running number from the last certificate
            $parts = explode('/', $lastRecord->nomor_sertifikat);
            $lastNumber = (int) end($parts);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        
        return $prefix . '/' . $year . '/' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Generate QR code URL for verification using UUID.
     * UUID-based URL is more secure and professional.
     */
    public function getVerificationUrl(): string
    {
        return url('/sertifikat/verify/' . $this->uuid);
    }

    /**
     * Get legacy verification URL (nomor sertifikat based).
     */
    public function getLegacyVerificationUrl(): string
    {
        return url('/sertifikat/verifikasi/' . urlencode($this->nomor_sertifikat));
    }

    /**
     * Check if certificate is still valid.
     */
    public function isValid(): bool
    {
        return $this->tanggal_berlaku_sampai >= now();
    }

    /**
     * Get validity status label.
     */
    public function getStatusValiditasAttribute(): string
    {
        return $this->isValid() ? 'Berlaku' : 'Kadaluarsa';
    }

    /**
     * Get validity status badge.
     */
    public function getStatusValiditasBadgeAttribute(): string
    {
        return $this->isValid() ? 'bg-gradient-success' : 'bg-gradient-danger';
    }
}
