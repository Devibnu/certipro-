<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PendaftaranSertifikasi extends Model
{
    use Auditable;

    /**
     * The table associated with the model.
     */
    protected $table = 'pendaftaran_sertifikasi';

    /**
     * Get the audit module name.
     */
    public function getAuditModule(): string
    {
        return 'pendaftaran';
    }

    /**
     * Status constants
     */
    const STATUS_BELUM_PILIH_SKEMA = 'BELUM_PILIH_SKEMA'; // Auto-created from Pra-Pendaftaran DITERIMA
    const STATUS_DRAFT = 'draft';
    const STATUS_DIAJUKAN = 'diajukan';
    const STATUS_DIVERIFIKASI = 'diverifikasi';
    const STATUS_DITOLAK = 'ditolak';
    const STATUS_SIAP_ASESMEN = 'siap_asesmen';
    const STATUS_BELUM_KOMPETEN = 'belum_kompeten';
    const STATUS_MENUNGGU_KEPUTUSAN = 'menunggu_keputusan';
    const STATUS_KOMPETEN_FINAL = 'kompeten_final';
    const STATUS_BELUM_KOMPETEN_FINAL = 'belum_kompeten_final';

    /**
     * Status Peserta constants (public-facing status)
     * Untuk transparansi kepada peserta sesuai BNSP & ISO 17024
     */
    const STATUS_PESERTA_DALAM_PROSES = 'DALAM_PROSES';
    const STATUS_PESERTA_DITERIMA = 'DITERIMA';
    const STATUS_PESERTA_DITOLAK = 'DITOLAK';
    const STATUS_PESERTA_DIJADWALKAN_ASESMEN = 'DIJADWALKAN_ASESMEN';
    const STATUS_PESERTA_SEDANG_ASESMEN = 'SEDANG_ASESMEN';
    const STATUS_PESERTA_MENUNGGU_KEPUTUSAN = 'MENUNGGU_KEPUTUSAN';
    const STATUS_PESERTA_LULUS_SERTIFIKASI = 'LULUS_SERTIFIKASI';
    const STATUS_PESERTA_TIDAK_LULUS = 'TIDAK_LULUS';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'pra_pendaftaran_id',
        'user_id',
        'skema_sertifikasi_id',
        'nomor_pendaftaran',
        'tanggal_daftar',
        'status',
        'status_peserta',
        'pesan_status_peserta',
        'status_peserta_updated_at',
        'catatan_admin',
        'nama_lengkap',
        'email',
        'no_hp',
        'tipe_peserta',
        'nik',
        'nim',
        'institusi',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'tanggal_daftar' => 'date',
        'status_peserta_updated_at' => 'datetime',
        'user_id' => 'integer',
        'skema_sertifikasi_id' => 'integer',
        'pra_pendaftaran_id' => 'integer',
    ];

    /**
     * Get the pra pendaftaran that this pendaftaran originated from.
     */
    public function praPendaftaran(): BelongsTo
    {
        return $this->belongsTo(PraPendaftaran::class, 'pra_pendaftaran_id');
    }

    /**
     * Get the user (asesi) that owns the pendaftaran.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Alias for user relation - used for asesmen module.
     * Returns user if available, otherwise creates virtual asesi from pra_pendaftaran or direct fields.
     */
    public function asesi(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get asesi name - handles both user and non-user asesi.
     */
    public function getAsesiNameAttribute(): string
    {
        if ($this->user) {
            return $this->user->name;
        }
        
        if ($this->praPendaftaran) {
            return $this->praPendaftaran->nama_lengkap ?? '-';
        }
        
        return $this->nama_lengkap ?? '-';
    }

    /**
     * Get asesi email - handles both user and non-user asesi.
     */
    public function getAsesiEmailAttribute(): string
    {
        if ($this->user) {
            return $this->user->email;
        }
        
        if ($this->praPendaftaran) {
            return $this->praPendaftaran->email ?? '-';
        }
        
        return $this->email ?? '-';
    }

    /**
     * Get the skema sertifikasi for the pendaftaran.
     */
    public function skemaSertifikasi(): BelongsTo
    {
        return $this->belongsTo(SkemaSertifikasi::class, 'skema_sertifikasi_id');
    }

    /**
     * Get the asesmen for the pendaftaran.
     */
    public function asesmen(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Asesmen::class, 'pendaftaran_id');
    }

    /**
     * Get the keputusan sertifikasi for the pendaftaran.
     */
    public function keputusan(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(KeputusanSertifikasi::class, 'pendaftaran_id');
    }

    /**
     * Get the sertifikat for the pendaftaran.
     */
    public function sertifikat(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Sertifikat::class, 'pendaftaran_id');
    }

    /**
     * Generate nomor pendaftaran unik.
     */
    public static function generateNomorPendaftaran(): string
    {
        $prefix = 'REG';
        $year = date('Y');
        $month = date('m');
        
        // Get last number for this month
        $lastRecord = self::where('nomor_pendaftaran', 'like', "{$prefix}{$year}{$month}%")
            ->orderBy('nomor_pendaftaran', 'desc')
            ->first();
        
        if ($lastRecord) {
            $lastNumber = (int) substr($lastRecord->nomor_pendaftaran, -4);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        
        return $prefix . $year . $month . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get all status labels.
     */
    public static function statusLabels(): array
    {
        return [
            self::STATUS_BELUM_PILIH_SKEMA => 'Belum Pilih Skema',
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_DIAJUKAN => 'Diajukan',
            self::STATUS_DIVERIFIKASI => 'Diverifikasi',
            self::STATUS_DITOLAK => 'Ditolak',
            self::STATUS_SIAP_ASESMEN => 'Siap Asesmen',
            self::STATUS_BELUM_KOMPETEN => 'Belum Kompeten',
            self::STATUS_MENUNGGU_KEPUTUSAN => 'Menunggu Keputusan',
            self::STATUS_KOMPETEN_FINAL => 'Kompeten (Final)',
            self::STATUS_BELUM_KOMPETEN_FINAL => 'Belum Kompeten (Final)',
        ];
    }

    /**
     * Get status label attribute.
     */
    public function getStatusLabelAttribute(): string
    {
        return self::statusLabels()[$this->status] ?? $this->status;
    }

    /**
     * Get status badge color.
     */
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            self::STATUS_BELUM_PILIH_SKEMA => 'bg-gradient-warning',
            self::STATUS_DRAFT => 'bg-gradient-secondary',
            self::STATUS_DIAJUKAN => 'bg-gradient-warning',
            self::STATUS_DIVERIFIKASI => 'bg-gradient-info',
            self::STATUS_DITOLAK => 'bg-gradient-danger',
            self::STATUS_SIAP_ASESMEN => 'bg-gradient-success',
            self::STATUS_BELUM_KOMPETEN => 'bg-gradient-danger',
            self::STATUS_MENUNGGU_KEPUTUSAN => 'bg-gradient-primary',
            self::STATUS_KOMPETEN_FINAL => 'bg-gradient-success',
            self::STATUS_BELUM_KOMPETEN_FINAL => 'bg-gradient-danger',
            default => 'bg-gradient-secondary',
        };
    }

    /**
     * Check if status is draft.
     */
    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    /**
     * Check if status is diajukan.
     */
    public function isDiajukan(): bool
    {
        return $this->status === self::STATUS_DIAJUKAN;
    }

    /**
     * Check if status is diverifikasi.
     */
    public function isDiverifikasi(): bool
    {
        return $this->status === self::STATUS_DIVERIFIKASI;
    }

    /**
     * Check if status is ditolak.
     */
    public function isDitolak(): bool
    {
        return $this->status === self::STATUS_DITOLAK;
    }

    /**
     * Check if status is siap asesmen.
     */
    public function isSiapAsesmen(): bool
    {
        return $this->status === self::STATUS_SIAP_ASESMEN;
    }

    /**
     * Scope untuk filter by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope untuk filter by user.
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Get all status peserta labels (public-facing).
     */
    public static function statusPesertaLabels(): array
    {
        return [
            self::STATUS_PESERTA_DALAM_PROSES => 'Dalam Proses',
            self::STATUS_PESERTA_DITERIMA => 'Diterima',
            self::STATUS_PESERTA_DITOLAK => 'Ditolak',
            self::STATUS_PESERTA_DIJADWALKAN_ASESMEN => 'Dijadwalkan Asesmen',
            self::STATUS_PESERTA_SEDANG_ASESMEN => 'Sedang Asesmen',
            self::STATUS_PESERTA_MENUNGGU_KEPUTUSAN => 'Menunggu Keputusan',
            self::STATUS_PESERTA_LULUS_SERTIFIKASI => 'Lulus Sertifikasi',
            self::STATUS_PESERTA_TIDAK_LULUS => 'Tidak Lulus',
        ];
    }

    /**
     * Get status peserta label attribute.
     */
    public function getStatusPesertaLabelAttribute(): string
    {
        return self::statusPesertaLabels()[$this->status_peserta] ?? $this->status_peserta;
    }

    /**
     * Get status peserta badge color.
     */
    public function getStatusPesertaBadgeAttribute(): string
    {
        return match($this->status_peserta) {
            self::STATUS_PESERTA_DALAM_PROSES => 'bg-warning text-dark',
            self::STATUS_PESERTA_DITERIMA => 'bg-info text-white',
            self::STATUS_PESERTA_DITOLAK => 'bg-danger text-white',
            self::STATUS_PESERTA_DIJADWALKAN_ASESMEN => 'bg-primary text-white',
            self::STATUS_PESERTA_SEDANG_ASESMEN => 'bg-secondary text-white',
            self::STATUS_PESERTA_MENUNGGU_KEPUTUSAN => 'bg-dark text-white',
            self::STATUS_PESERTA_LULUS_SERTIFIKASI => 'bg-success text-white',
            self::STATUS_PESERTA_TIDAK_LULUS => 'bg-danger text-white',
            default => 'bg-secondary text-white',
        };
    }

    /**
     * Get human-readable message for status peserta.
     */
    public static function getStatusPesertaMessage(string $statusPeserta): string
    {
        return match($statusPeserta) {
            self::STATUS_PESERTA_DALAM_PROSES => 
                'Pendaftaran Anda sedang dalam proses verifikasi oleh tim LSP. Mohon tunggu informasi selanjutnya.',
            self::STATUS_PESERTA_DITERIMA => 
                'Selamat! Pendaftaran Anda telah diterima dan diverifikasi. Silakan tunggu jadwal asesmen.',
            self::STATUS_PESERTA_DITOLAK => 
                'Mohon maaf, pendaftaran Anda tidak dapat diproses. Silakan hubungi LSP untuk informasi lebih lanjut.',
            self::STATUS_PESERTA_DIJADWALKAN_ASESMEN => 
                'Asesmen Anda telah dijadwalkan. Silakan persiapkan diri dan hadir sesuai jadwal yang ditentukan.',
            self::STATUS_PESERTA_SEDANG_ASESMEN => 
                'Proses asesmen kompetensi sedang berlangsung. Mohon ikuti instruksi dari asesor.',
            self::STATUS_PESERTA_MENUNGGU_KEPUTUSAN => 
                'Asesmen telah selesai. Hasil sedang dalam proses keputusan oleh Komite Teknis.',
            self::STATUS_PESERTA_LULUS_SERTIFIKASI => 
                'Selamat! Anda dinyatakan KOMPETEN dan berhak mendapatkan sertifikat kompetensi.',
            self::STATUS_PESERTA_TIDAK_LULUS => 
                'Berdasarkan hasil asesmen, Anda dinyatakan BELUM KOMPETEN. Silakan hubungi LSP untuk informasi re-asesmen.',
            default => 'Status tidak diketahui.',
        };
    }

    /**
     * Map internal status to status peserta.
     */
    public static function mapToStatusPeserta(string $internalStatus): string
    {
        return match($internalStatus) {
            self::STATUS_DRAFT, self::STATUS_DIAJUKAN => self::STATUS_PESERTA_DALAM_PROSES,
            self::STATUS_DIVERIFIKASI => self::STATUS_PESERTA_DITERIMA,
            self::STATUS_DITOLAK => self::STATUS_PESERTA_DITOLAK,
            self::STATUS_SIAP_ASESMEN => self::STATUS_PESERTA_DIJADWALKAN_ASESMEN,
            self::STATUS_BELUM_KOMPETEN => self::STATUS_PESERTA_SEDANG_ASESMEN,
            self::STATUS_MENUNGGU_KEPUTUSAN => self::STATUS_PESERTA_MENUNGGU_KEPUTUSAN,
            self::STATUS_KOMPETEN_FINAL => self::STATUS_PESERTA_LULUS_SERTIFIKASI,
            self::STATUS_BELUM_KOMPETEN_FINAL => self::STATUS_PESERTA_TIDAK_LULUS,
            default => self::STATUS_PESERTA_DALAM_PROSES,
        };
    }

    /**
     * Update status peserta based on internal status.
     * This method should be called whenever internal status changes.
     */
    public function updateStatusPeserta(): void
    {
        $newStatusPeserta = self::mapToStatusPeserta($this->status);
        
        // Check if status is final (immutable)
        $finalStatuses = [
            self::STATUS_PESERTA_LULUS_SERTIFIKASI,
            self::STATUS_PESERTA_TIDAK_LULUS,
        ];
        
        // Don't update if current status is already final
        if (in_array($this->status_peserta, $finalStatuses)) {
            return;
        }
        
        $this->status_peserta = $newStatusPeserta;
        $this->pesan_status_peserta = self::getStatusPesertaMessage($newStatusPeserta);
        $this->status_peserta_updated_at = now();
        $this->saveQuietly(); // Save without triggering events
    }

    /**
     * Check if status peserta is final (immutable).
     */
    public function isStatusPesertaFinal(): bool
    {
        return in_array($this->status_peserta, [
            self::STATUS_PESERTA_LULUS_SERTIFIKASI,
            self::STATUS_PESERTA_TIDAK_LULUS,
        ]);
    }

    /**
     * Scope for public status search.
     */
    public function scopePublicSearch($query, string $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('nomor_pendaftaran', $search)
              ->orWhere('email', $search);
        });
    }
}
