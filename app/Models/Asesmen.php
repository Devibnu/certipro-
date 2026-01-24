<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asesmen extends Model
{
    use Auditable;

    /**
     * The table associated with the model.
     */
    protected $table = 'asesmen';

    /**
     * Get the audit module name.
     */
    public function getAuditModule(): string
    {
        return 'asesmen';
    }

    /**
     * Status constants
     */
    const STATUS_PROSES = 'proses';
    const STATUS_SELESAI = 'selesai';

    /**
     * Metode asesmen constants
     */
    const METODE_OBSERVASI = 'observasi';
    const METODE_PORTOFOLIO = 'portofolio';
    const METODE_WAWANCARA = 'wawancara';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'pendaftaran_id',
        'asesor_id',
        'tanggal_asesmen',
        'metode_asesmen',
        'catatan_asesor',
        'status',
        // Sampling Audit fields
        'is_sampled',
        'sampled_at',
        'sampled_by',
        'sampling_note',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'tanggal_asesmen' => 'date',
        'pendaftaran_id' => 'integer',
        'asesor_id' => 'integer',
        'is_sampled' => 'boolean',
        'sampled_at' => 'datetime',
        'sampled_by' => 'integer',
    ];

    /**
     * Get the pendaftaran that owns the asesmen.
     */
    public function pendaftaran(): BelongsTo
    {
        return $this->belongsTo(PendaftaranSertifikasi::class, 'pendaftaran_id');
    }

    /**
     * Get the asesor (user) that owns the asesmen.
     */
    public function asesor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asesor_id');
    }

    /**
     * Get the user who marked this asesmen for sampling.
     */
    public function sampledByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sampled_by');
    }

    /**
     * Get the asesmen details.
     */
    public function details(): HasMany
    {
        return $this->hasMany(AsesmenDetail::class, 'asesmen_id');
    }

    /**
     * Get the evidence files/links for this asesmen.
     */
    public function evidences(): HasMany
    {
        return $this->hasMany(EvidenceKuk::class, 'asesmen_id');
    }

    /**
     * Check if asesmen is locked (selesai = locked).
     * Evidence cannot be added/deleted when locked.
     */
    public function isLocked(): bool
    {
        return $this->status === self::STATUS_SELESAI;
    }

    /**
     * Get metode labels.
     */
    public static function metodeLabels(): array
    {
        return [
            self::METODE_OBSERVASI => 'Observasi',
            self::METODE_PORTOFOLIO => 'Portofolio',
            self::METODE_WAWANCARA => 'Wawancara',
        ];
    }

    /**
     * Get status labels.
     */
    public static function statusLabels(): array
    {
        return [
            self::STATUS_PROSES => 'Dalam Proses',
            self::STATUS_SELESAI => 'Selesai',
        ];
    }

    /**
     * Get metode label attribute.
     */
    public function getMetodeLabelAttribute(): string
    {
        return self::metodeLabels()[$this->metode_asesmen] ?? $this->metode_asesmen;
    }

    /**
     * Get status label attribute.
     */
    public function getStatusLabelAttribute(): string
    {
        return self::statusLabels()[$this->status] ?? $this->status;
    }

    /**
     * Get status badge attribute.
     */
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PROSES => 'bg-gradient-warning',
            self::STATUS_SELESAI => 'bg-gradient-success',
            default => 'bg-gradient-secondary',
        };
    }

    /**
     * Check if asesmen is in progress.
     */
    public function isProses(): bool
    {
        return $this->status === self::STATUS_PROSES;
    }

    /**
     * Check if asesmen is complete.
     */
    public function isSelesai(): bool
    {
        return $this->status === self::STATUS_SELESAI;
    }

    /**
     * Check if all KUK are kompeten.
     */
    public function isAllKompeten(): bool
    {
        return $this->details()->where('hasil', 'belum_kompeten')->count() === 0;
    }

    /**
     * Scope by asesor.
     */
    public function scopeByAsesor($query, $asesorId)
    {
        return $query->where('asesor_id', $asesorId);
    }

    /**
     * Scope to filter only sampled asesmen.
     */
    public function scopeSampled($query)
    {
        return $query->where('is_sampled', true);
    }

    /**
     * Scope to filter non-sampled asesmen.
     */
    public function scopeNotSampled($query)
    {
        return $query->where('is_sampled', false);
    }

    /**
     * Check if asesmen is marked for sampling audit.
     */
    public function isSampled(): bool
    {
        return (bool) $this->is_sampled;
    }

    /**
     * Check if sampling can be modified.
     * Sampling can only be modified if:
     * - Asesmen is selesai (completed)
     * - Keputusan is not locked yet
     */
    public function canModifySampling(): bool
    {
        // Must be completed first
        if (!$this->isSelesai()) {
            return false;
        }

        // Check if keputusan is locked
        $keputusan = $this->pendaftaran?->keputusanSertifikasi;
        if ($keputusan && $keputusan->isLocked()) {
            return false;
        }

        return true;
    }

    /**
     * Get sampling status label.
     */
    public function getSamplingStatusLabelAttribute(): string
    {
        return $this->is_sampled ? 'Sampling Audit' : 'Tidak Disampling';
    }

    /**
     * Get sampling badge class.
     */
    public function getSamplingBadgeAttribute(): string
    {
        return $this->is_sampled ? 'bg-gradient-info' : 'bg-secondary';
    }
}
