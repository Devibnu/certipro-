<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BagianHalaman extends Model
{
    protected $table = 'bagian_halaman';

    protected $fillable = [
        'halaman_id',
        'tipe',
        'judul',
        'isi',
        'urutan',
        'aktif',
    ];

    protected $casts = [
        'aktif' => 'boolean',
        'urutan' => 'integer',
    ];

    /**
     * Get halaman yang memiliki bagian ini
     */
    public function halaman(): BelongsTo
    {
        return $this->belongsTo(Halaman::class, 'halaman_id');
    }

    /**
     * Get item-item bagian halaman
     */
    public function itemBagianHalaman(): HasMany
    {
        return $this->hasMany(ItemBagianHalaman::class, 'bagian_halaman_id')->orderBy('urutan');
    }

    /**
     * Scope untuk bagian halaman aktif
     */
    public function scopeAktif($query)
    {
        return $query->where('aktif', true);
    }
}
