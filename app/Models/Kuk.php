<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Kuk extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'kuk';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'unit_kompetensi_id',
        'kode_kuk',
        'pernyataan_unjuk_kerja',
        'urutan',
        'aktif',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'aktif' => 'boolean',
        'urutan' => 'integer',
        'unit_kompetensi_id' => 'integer',
    ];

    /**
     * Get the unit kompetensi that owns the KUK.
     */
    public function unitKompetensi(): BelongsTo
    {
        return $this->belongsTo(UnitKompetensi::class, 'unit_kompetensi_id');
    }

    /**
     * Scope untuk KUK aktif.
     */
    public function scopeAktif($query)
    {
        return $query->where('aktif', true);
    }

    /**
     * Scope untuk filter by unit kompetensi.
     */
    public function scopeByUnit($query, $unitId)
    {
        return $query->where('unit_kompetensi_id', $unitId);
    }

    /**
     * Get status label attribute.
     */
    public function getStatusLabelAttribute(): string
    {
        return $this->aktif ? 'Aktif' : 'Nonaktif';
    }
}
