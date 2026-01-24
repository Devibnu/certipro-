<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UnitKompetensi extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'unit_kompetensi';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'skema_sertifikasi_id',
        'kode_unit',
        'nama_unit',
        'deskripsi',
        'aktif',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'aktif' => 'boolean',
        'skema_sertifikasi_id' => 'integer',
    ];

    /**
     * Get the skema sertifikasi that owns the unit kompetensi.
     */
    public function skemaSertifikasi(): BelongsTo
    {
        return $this->belongsTo(SkemaSertifikasi::class, 'skema_sertifikasi_id');
    }

    /**
     * Scope untuk unit kompetensi aktif.
     */
    public function scopeAktif($query)
    {
        return $query->where('aktif', true);
    }

    /**
     * Get status label attribute.
     */
    public function getStatusLabelAttribute(): string
    {
        return $this->aktif ? 'Aktif' : 'Nonaktif';
    }

    /**
     * Normalize kode_unit to uppercase and trimmed.
     * This ensures data consistency at model level.
     */
    public function setKodeUnitAttribute($value): void
    {
        $this->attributes['kode_unit'] = strtoupper(trim($value ?? ''));
    }

    /**
     * Get the KUK (Kriteria Unjuk Kerja) for the unit kompetensi.
     */
    public function kuk(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Kuk::class, 'unit_kompetensi_id');
    }

    /**
     * Alias for kuk() relationship - untuk kompatibilitas.
     */
    public function kuks(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->kuk();
    }
}
