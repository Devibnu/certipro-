<?php

namespace App\Models;

use App\Models\User;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PraPendaftaran extends Model
{
    use HasFactory, Auditable;

    /**
     * The table associated with the model.
     */
    protected $table = 'pra_pendaftaran';

    /**
     * Get the audit module name.
     */
    public function getAuditModule(): string
    {
        return 'pra_pendaftaran';
    }

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'nomor_pra_pendaftaran',
        'nama_lengkap',
        'email',
        'no_hp',
        'tipe_peserta',
        'nik',
        'nim',
        'institusi',
        'upload_identitas',
        'status',
        'alasan_penolakan',
        'status_updated_at',
        'status_updated_by',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'status_updated_at' => 'datetime',
    ];

    /**
     * ========================================================================
     * STATUS CONSTANTS - ONLY 3 VALID STATES (Clean Architecture)
     * ========================================================================
     * Prinsip:
     * - Pra-Pendaftaran BUKAN proses sertifikasi
     * - Hanya untuk validasi data awal
     * - Tidak boleh ada status ambigu
     */
    const STATUS_MENUNGGU_VERIFIKASI = 'menunggu_verifikasi';
    const STATUS_DITERIMA = 'diterima';
    const STATUS_DITOLAK = 'ditolak';

    /**
     * Status labels for admin display
     */
    public static function statusLabels(): array
    {
        return [
            self::STATUS_MENUNGGU_VERIFIKASI => 'Menunggu Verifikasi',
            self::STATUS_DITERIMA => 'Diterima (Menunggu Penetapan Skema)',
            self::STATUS_DITOLAK => 'Ditolak',
        ];
    }

    /**
     * Status labels for public display (peserta-facing)
     */
    public static function statusPublicLabels(): array
    {
        return [
            self::STATUS_MENUNGGU_VERIFIKASI => 'MENUNGGU VERIFIKASI',
            self::STATUS_DIPROSES => 'SEDANG DIVERIFIKASI',
            self::STATUS_DITERIMA => 'DITERIMA',
            self::STATUS_DITOLAK => 'DITOLAK',
        ];
    }

    /**
     * Get status public label attribute
     */
    public function getStatusPublicLabelAttribute(): string
    {
        return self::statusPublicLabels()[$this->status] ?? strtoupper($this->status);
    }

    /**
     * Status colors for UI
     */
    public static function statusColors(): array
    {
        return [
            self::STATUS_BARU => 'warning',      // Kuning
            self::STATUS_DIPROSES => 'info',     // Biru
            self::STATUS_DITERIMA => 'success',  // Hijau
            self::STATUS_DITOLAK => 'danger',    // Merah
        ];
    }

    /**
     * Get status color attribute
     */
    public function getStatusColorAttribute(): string
    {
        return self::statusColors()[$this->status] ?? 'secondary';
    }

    /**
     * Status icons for UI
     */
    public static function statusIcons(): array
    {
        return [
            self::STATUS_BARU => 'fa-clock',
            self::STATUS_DIPROSES => 'fa-spinner',
            self::STATUS_DITERIMA => 'fa-check-circle',
            self::STATUS_DITOLAK => 'fa-times-circle',
        ];
    }

    /**
     * Get status icon attribute
     */
    public function getStatusIconAttribute(): string
    {
        return self::statusIcons()[$this->status] ?? 'fa-question-circle';
    }

    /**
     * Get status message for peserta
     */
    public function getStatusMessageAttribute(): string
    {
        $messages = [
            self::STATUS_BARU => 'Pendaftaran Anda telah kami terima dan sedang menunggu proses verifikasi oleh tim kami. Harap menunggu dalam 1-3 hari kerja.',
            self::STATUS_DIPROSES => 'Data pendaftaran Anda sedang dalam proses verifikasi oleh tim verifikator. Kami akan segera menginformasikan hasilnya.',
            self::STATUS_DITERIMA => 'Selamat! Pendaftaran Anda telah diverifikasi dan diterima. Silakan lanjutkan ke tahap pendaftaran sertifikasi untuk memilih skema dan jadwal asesmen.',
            self::STATUS_DITOLAK => 'Mohon maaf, pendaftaran Anda tidak dapat kami terima. Silakan periksa alasan penolakan di bawah ini dan hubungi kami jika ada pertanyaan.',
        ];

        return $messages[$this->status] ?? 'Status tidak diketahui.';
    }

    /**
     * Get timeline step number (1-based)
     */
    public function getTimelineStepAttribute(): int
    {
        $steps = [
            self::STATUS_BARU => 1,
            self::STATUS_DIPROSES => 2,
            self::STATUS_DITERIMA => 3,
            self::STATUS_DITOLAK => 3,
        ];

        return $steps[$this->status] ?? 1;
    }

    /**
     * Check if status allows continuation to pendaftaran sertifikasi
     */
    public function canProceedToSertifikasi(): bool
    {
        return $this->status === self::STATUS_DITERIMA && !$this->hasPendaftaranSertifikasi();
    }

    /**
     * Check if status is final (cannot be changed)
     */
    public function isStatusFinal(): bool
    {
        return in_array($this->status, [self::STATUS_DITERIMA, self::STATUS_DITOLAK]);
    }

    /**
     * Update status with audit trail
     */
    public function updateStatus(string $newStatus, ?string $alasanPenolakan = null, ?int $userId = null): bool
    {
        $oldStatus = $this->status;

        $this->status = $newStatus;
        $this->status_updated_at = now();
        $this->status_updated_by = $userId ?? auth()->id();

        if ($newStatus === self::STATUS_DITOLAK && $alasanPenolakan) {
            $this->alasan_penolakan = $alasanPenolakan;
        }

        return $this->save();
    }

    /**
     * Get status label attribute
     */
    public function getStatusLabelAttribute(): string
    {
        return self::statusLabels()[$this->status] ?? $this->status;
    }

    /**
     * Tipe peserta labels
     */
    public static function tipePesertaLabels(): array
    {
        return [
            'umum' => 'Umum',
            'kampus' => 'Kampus',
        ];
    }

    /**
     * Get tipe peserta label attribute
     */
    public function getTipePesertaLabelAttribute(): string
    {
        return self::tipePesertaLabels()[$this->tipe_peserta] ?? $this->tipe_peserta;
    }

    /**
     * Get the pendaftaran sertifikasi created from this pra-pendaftaran.
     */
    public function pendaftaranSertifikasi()
    {
        return $this->hasOne(PendaftaranSertifikasi::class, 'pra_pendaftaran_id');
    }

    /**
     * Get the user who last updated the status.
     */
    public function statusUpdatedBy()
    {
        return $this->belongsTo(User::class, 'status_updated_by');
    }

    /**
     * Check if pendaftaran sertifikasi already exists.
     */
    public function hasPendaftaranSertifikasi(): bool
    {
        return $this->pendaftaranSertifikasi()->exists();
    }
}
