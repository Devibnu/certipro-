<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AsesmenDetail extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'asesmen_detail';

    /**
     * Hasil constants
     */
    const HASIL_KOMPETEN = 'kompeten';
    const HASIL_BELUM_KOMPETEN = 'belum_kompeten';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'asesmen_id',
        'unit_kompetensi_id',
        'kuk_id',
        'hasil',
        'catatan',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'asesmen_id' => 'integer',
        'unit_kompetensi_id' => 'integer',
        'kuk_id' => 'integer',
    ];

    /**
     * Get the asesmen that owns the detail.
     */
    public function asesmen(): BelongsTo
    {
        return $this->belongsTo(Asesmen::class, 'asesmen_id');
    }

    /**
     * Get the unit kompetensi.
     */
    public function unitKompetensi(): BelongsTo
    {
        return $this->belongsTo(UnitKompetensi::class, 'unit_kompetensi_id');
    }

    /**
     * Get the KUK.
     */
    public function kuk(): BelongsTo
    {
        return $this->belongsTo(Kuk::class, 'kuk_id');
    }

    /**
     * Get hasil labels.
     */
    public static function hasilLabels(): array
    {
        return [
            self::HASIL_KOMPETEN => 'Kompeten',
            self::HASIL_BELUM_KOMPETEN => 'Belum Kompeten',
        ];
    }

    /**
     * Get hasil label attribute.
     */
    public function getHasilLabelAttribute(): string
    {
        return self::hasilLabels()[$this->hasil] ?? $this->hasil;
    }

    /**
     * Get hasil badge attribute.
     */
    public function getHasilBadgeAttribute(): string
    {
        return match($this->hasil) {
            self::HASIL_KOMPETEN => 'bg-gradient-success',
            self::HASIL_BELUM_KOMPETEN => 'bg-gradient-danger',
            default => 'bg-gradient-secondary',
        };
    }

    /**
     * Check if hasil is kompeten.
     */
    public function isKompeten(): bool
    {
        return $this->hasil === self::HASIL_KOMPETEN;
    }

    /**
     * Check if hasil is belum kompeten.
     */
    public function isBelumKompeten(): bool
    {
        return $this->hasil === self::HASIL_BELUM_KOMPETEN;
    }
}
