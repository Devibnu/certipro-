<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemBagianHalaman extends Model
{
    protected $table = 'item_bagian_halaman';

    protected $fillable = [
        'bagian_halaman_id',
        'judul',
        'subjudul',
        'deskripsi',
        'ikon',
        'gambar',
        'tombol_text',
        'tombol_link',
        'tombol_text_2',
        'tombol_link_2',
        'urutan',
        'aktif',
    ];

    protected $casts = [
        'urutan' => 'integer',
        'aktif' => 'boolean',
    ];

    /**
     * Get bagian halaman yang memiliki item ini
     */
    public function bagianHalaman(): BelongsTo
    {
        return $this->belongsTo(BagianHalaman::class, 'bagian_halaman_id');
    }
}
