<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KeputusanSertifikasi extends Model
{
    use Auditable;

    /**
     * The table associated with the model.
     */
    protected $table = 'keputusan_sertifikasi';

    /**
     * Get the audit module name.
     */
    public function getAuditModule(): string
    {
        return 'keputusan';
    }

    /**
     * Keputusan constants
     */
    const KEPUTUSAN_KOMPETEN = 'kompeten';
    const KEPUTUSAN_BELUM_KOMPETEN = 'belum_kompeten';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'pendaftaran_id',
        'asesmen_id',
        'keputusan',
        'catatan_komite',
        'ditetapkan_oleh',
        'tanggal_keputusan',
        'is_locked',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'tanggal_keputusan' => 'date',
        'is_locked' => 'boolean',
        'pendaftaran_id' => 'integer',
        'asesmen_id' => 'integer',
        'ditetapkan_oleh' => 'integer',
    ];

    /**
     * Get the pendaftaran that owns the keputusan.
     */
    public function pendaftaran(): BelongsTo
    {
        return $this->belongsTo(PendaftaranSertifikasi::class, 'pendaftaran_id');
    }

    /**
     * Get the asesmen that owns the keputusan.
     */
    public function asesmen(): BelongsTo
    {
        return $this->belongsTo(Asesmen::class, 'asesmen_id');
    }

    /**
     * Get the user (admin) who made the decision.
     */
    public function penetap(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ditetapkan_oleh');
    }

    /**
     * Get all keputusan labels.
     */
    public static function keputusanLabels(): array
    {
        return [
            self::KEPUTUSAN_KOMPETEN => 'Kompeten',
            self::KEPUTUSAN_BELUM_KOMPETEN => 'Belum Kompeten',
        ];
    }

    /**
     * Get keputusan label attribute.
     */
    public function getKeputusanLabelAttribute(): string
    {
        return self::keputusanLabels()[$this->keputusan] ?? $this->keputusan;
    }

    /**
     * Get keputusan badge attribute.
     */
    public function getKeputusanBadgeAttribute(): string
    {
        return match($this->keputusan) {
            self::KEPUTUSAN_KOMPETEN => 'bg-gradient-success',
            self::KEPUTUSAN_BELUM_KOMPETEN => 'bg-gradient-danger',
            default => 'bg-gradient-secondary',
        };
    }

    /**
     * Check if keputusan is kompeten.
     */
    public function isKompeten(): bool
    {
        return $this->keputusan === self::KEPUTUSAN_KOMPETEN;
    }

    /**
     * Check if keputusan is belum kompeten.
     */
    public function isBelumKompeten(): bool
    {
        return $this->keputusan === self::KEPUTUSAN_BELUM_KOMPETEN;
    }

    /**
     * Check if keputusan is locked.
     */
    public function isLocked(): bool
    {
        return $this->is_locked === true;
    }

    /**
     * Lock the keputusan.
     */
    public function lock(): bool
    {
        $this->is_locked = true;
        return $this->save();
    }
}
