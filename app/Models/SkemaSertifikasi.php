<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class SkemaSertifikasi extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'skema_sertifikasi';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'kode_skema',
        'nama_skema',
        'deskripsi',
        'jenis',
        'masa_berlaku',
        'aktif',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'aktif' => 'boolean',
        'masa_berlaku' => 'integer',
    ];

    /**
     * Scope untuk filter skema yang aktif
     */
    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('aktif', true);
    }

    /**
     * Jenis labels for display
     */
    public static function jenisLabels(): array
    {
        return [
            'nasional' => 'Nasional',
            'internasional' => 'Internasional',
            'internal' => 'Internal',
        ];
    }

    /**
     * Get jenis label attribute
     */
    public function getJenisLabelAttribute(): string
    {
        return self::jenisLabels()[$this->jenis] ?? $this->jenis;
    }

    /**
     * Get status label attribute
     */
    public function getStatusLabelAttribute(): string
    {
        return $this->aktif ? 'Aktif' : 'Nonaktif';
    }

    /**
     * Get the unit kompetensi for the skema sertifikasi.
     */
    public function unitKompetensi(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UnitKompetensi::class, 'skema_sertifikasi_id');
    }

    /**
     * Get the pendaftaran sertifikasi for the skema.
     */
    public function pendaftaranSertifikasi(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PendaftaranSertifikasi::class, 'skema_sertifikasi_id');
    }
}
