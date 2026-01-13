<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Halaman extends Model
{
    protected $table = 'halaman';

    protected $fillable = [
        'slug',
        'judul',
        'aktif',
    ];

    protected $casts = [
        'aktif' => 'boolean',
    ];

    /**
     * Get bagian-bagian halaman
     */
    public function bagianHalaman(): HasMany
    {
        return $this->hasMany(BagianHalaman::class, 'halaman_id')->orderBy('urutan');
    }

    /**
     * Get bagian halaman yang aktif saja
     */
    public function bagianHalamanAktif(): HasMany
    {
        return $this->hasMany(BagianHalaman::class, 'halaman_id')
            ->where('aktif', true)
            ->orderBy('urutan');
    }

    /**
     * Scope untuk halaman aktif
     */
    public function scopeAktif($query)
    {
        return $query->where('aktif', true);
    }

    /**
     * Get halaman by slug
     */
    public function scopeBySlug($query, $slug)
    {
        return $query->where('slug', $slug);
    }
}
