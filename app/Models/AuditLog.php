<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLog extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'audit_logs';

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'user_name',
        'user_role',
        'action',
        'module',
        'description',
        'model_type',
        'model_id',
        'reference_number',
        'old_values',
        'new_values',
        'metadata',
        'ip_address',
        'user_agent',
        'url',
        'method',
        'created_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Action constants
     */
    const ACTION_CREATE = 'create';
    const ACTION_UPDATE = 'update';
    const ACTION_DELETE = 'delete';
    const ACTION_VIEW = 'view';
    const ACTION_APPROVE = 'approve';
    const ACTION_REJECT = 'reject';
    const ACTION_VERIFY = 'verify';
    const ACTION_ASSESS = 'assess';
    const ACTION_DECIDE = 'decide';
    const ACTION_ISSUE = 'issue';
    const ACTION_REVOKE = 'revoke';
    const ACTION_LOGIN = 'login';
    const ACTION_LOGOUT = 'logout';
    const ACTION_PASSWORD_RESET_BY_ADMIN = 'password_reset_by_admin';
    const ACTION_PASSWORD_RESET_BY_USER = 'password_reset_by_user';
    const ACTION_PASSWORD_RESET_REQUESTED = 'password_reset_requested';
    const ACTION_PASSWORD_RESET_EMAIL_SENT = 'password_reset_email_sent';
    const ACTION_PASSWORD_RESET_FAILED = 'password_reset_failed';
    const ACTION_PASSWORD_RESET_SUCCESS = 'password_reset_success';
    
    // RBAC Actions - ISO 17024 Compliant
    const ACTION_ACCESS_DENIED = 'access_denied';
    const ACTION_ROLE_ASSIGNED = 'role_assigned';
    const ACTION_ROLE_REMOVED = 'role_removed';
    const ACTION_ROLES_SYNCED = 'roles_synced';
    const ACTION_PERMISSION_GRANTED = 'permission_granted';
    const ACTION_PERMISSION_REVOKED = 'permission_revoked';
    
    // Evidence Actions - ISO 17024 Audit Trail
    const ACTION_EVIDENCE_UPLOADED = 'evidence_uploaded';
    const ACTION_EVIDENCE_DELETED = 'evidence_deleted';
    const ACTION_EVIDENCE_LINK_ADDED = 'evidence_link_added';
    const ACTION_EVIDENCE_DOWNLOADED = 'evidence_downloaded';
    
    // Sampling Audit Actions - ISO 17024 Quality Control
    const ACTION_SAMPLING_MARKED = 'asesmen_marked_sampling';
    const ACTION_SAMPLING_UNMARKED = 'asesmen_unmarked_sampling';
    const ACTION_SAMPLING_NOTE_UPDATED = 'asesmen_sampling_note_updated';

    /**
     * Module constants
     */
    const MODULE_AUTH = 'auth';
    const MODULE_ACCESS_CONTROL = 'access_control';
    const MODULE_RBAC = 'rbac';
    const MODULE_EVIDENCE = 'evidence';
    const MODULE_SAMPLING = 'sampling';
    const MODULE_PRA_PENDAFTARAN = 'pra_pendaftaran';
    const MODULE_PENDAFTARAN = 'pendaftaran';
    const MODULE_ASESMEN = 'asesmen';
    const MODULE_KEPUTUSAN = 'keputusan';
    const MODULE_SERTIFIKAT = 'sertifikat';
    const MODULE_USER = 'user';
    const MODULE_SKEMA = 'skema';
    const MODULE_SETTINGS = 'settings';

    /**
     * Get the user who performed the action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the related model.
     */
    public function auditable()
    {
        if ($this->model_type && $this->model_id) {
            return $this->model_type::find($this->model_id);
        }
        return null;
    }

    /**
     * Log an action to the audit trail.
     * 
     * @param string $action Action performed
     * @param string $module Module name
     * @param string $description Human-readable description
     * @param Model|null $model The model being acted upon
     * @param array|null $oldValues Previous values (for updates)
     * @param array|null $newValues New values (for creates/updates)
     * @param array|null $metadata Additional metadata
     * @return AuditLog
     */
    public static function log(
        string $action,
        string $module,
        string $description,
        ?Model $model = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?array $metadata = null
    ): self {
        $user = Auth::user();
        
        // Determine reference number from model
        $referenceNumber = null;
        if ($model) {
            $referenceNumber = $model->nomor_pra_pendaftaran 
                ?? $model->nomor_pendaftaran 
                ?? $model->nomor_sertifikat 
                ?? $model->kode_skema 
                ?? null;
        }
        
        return self::create([
            'user_id' => $user?->id,
            'user_name' => $user?->name ?? 'System',
            'user_role' => $user?->role ?? 'system',
            'action' => $action,
            'module' => $module,
            'description' => $description,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model?->id,
            'reference_number' => $referenceNumber,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'metadata' => $metadata,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'url' => Request::fullUrl(),
            'method' => Request::method(),
            'created_at' => now(),
        ]);
    }

    /**
     * Get action label in Indonesian.
     */
    public function getActionLabelAttribute(): string
    {
        return match($this->action) {
            self::ACTION_CREATE => 'Membuat',
            self::ACTION_UPDATE => 'Mengubah',
            self::ACTION_DELETE => 'Menghapus',
            self::ACTION_VIEW => 'Melihat',
            self::ACTION_APPROVE => 'Menyetujui',
            self::ACTION_REJECT => 'Menolak',
            self::ACTION_VERIFY => 'Memverifikasi',
            self::ACTION_ASSESS => 'Mengasesmen',
            self::ACTION_DECIDE => 'Menetapkan Keputusan',
            self::ACTION_ISSUE => 'Menerbitkan',
            self::ACTION_REVOKE => 'Mencabut',
            self::ACTION_LOGIN => 'Login',
            self::ACTION_LOGOUT => 'Logout',
            default => ucfirst($this->action),
        };
    }

    /**
     * Get module label in Indonesian.
     */
    public function getModuleLabelAttribute(): string
    {
        return match($this->module) {
            self::MODULE_AUTH => 'Autentikasi',
            self::MODULE_PRA_PENDAFTARAN => 'Pra-Pendaftaran',
            self::MODULE_PENDAFTARAN => 'Pendaftaran Sertifikasi',
            self::MODULE_ASESMEN => 'Asesmen',
            self::MODULE_KEPUTUSAN => 'Keputusan Sertifikasi',
            self::MODULE_SERTIFIKAT => 'Sertifikat',
            self::MODULE_USER => 'Pengguna',
            self::MODULE_SKEMA => 'Skema Sertifikasi',
            default => ucfirst($this->module),
        };
    }

    /**
     * Get action badge color.
     */
    public function getActionBadgeAttribute(): string
    {
        return match($this->action) {
            self::ACTION_CREATE => 'bg-gradient-success',
            self::ACTION_UPDATE => 'bg-gradient-info',
            self::ACTION_DELETE => 'bg-gradient-danger',
            self::ACTION_APPROVE, self::ACTION_VERIFY => 'bg-gradient-success',
            self::ACTION_REJECT => 'bg-gradient-danger',
            self::ACTION_ASSESS => 'bg-gradient-warning',
            self::ACTION_DECIDE => 'bg-gradient-primary',
            self::ACTION_ISSUE => 'bg-gradient-success',
            self::ACTION_REVOKE => 'bg-gradient-danger',
            self::ACTION_LOGIN => 'bg-gradient-info',
            self::ACTION_LOGOUT => 'bg-gradient-secondary',
            default => 'bg-gradient-secondary',
        };
    }

    /**
     * Scope to filter by module.
     */
    public function scopeModule($query, string $module)
    {
        return $query->where('module', $module);
    }

    /**
     * Scope to filter by action.
     */
    public function scopeAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope to filter by user.
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to get logs for a specific model.
     */
    public function scopeForModel($query, Model $model)
    {
        return $query->where('model_type', get_class($model))
                     ->where('model_id', $model->id);
    }

    /**
     * Scope to filter by event type from metadata.
     */
    public function scopeEvent($query, string $event)
    {
        return $query->whereJsonContains('metadata->event', $event);
    }

    /**
     * Log public verification access.
     * This is for tracking when someone verifies a certificate publicly.
     */
    public static function logVerifikasiPublik(Sertifikat $sertifikat): self
    {
        return self::create([
            'user_id' => null,
            'user_name' => 'Public',
            'user_role' => 'public',
            'action' => self::ACTION_VIEW,
            'module' => self::MODULE_SERTIFIKAT,
            'description' => "Verifikasi publik sertifikat: {$sertifikat->nomor_sertifikat}",
            'model_type' => Sertifikat::class,
            'model_id' => $sertifikat->id,
            'reference_number' => $sertifikat->nomor_sertifikat,
            'old_values' => null,
            'new_values' => null,
            'metadata' => [
                'event' => 'verifikasi_publik_diakses',
                'uuid' => $sertifikat->uuid,
                'nama_peserta' => $sertifikat->nama_peserta,
            ],
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'url' => Request::fullUrl(),
            'method' => Request::method(),
            'created_at' => now(),
        ]);
    }

    /**
     * Get all available events for filtering.
     */
    public static function getEventTypes(): array
    {
        return [
            // Pra-Pendaftaran Events
            'pra_pendaftaran_dibuat' => 'Pra-Pendaftaran Dibuat',
            'pra_pendaftaran_diproses' => 'Pra-Pendaftaran Diproses',
            'pra_pendaftaran_diterima' => 'Pra-Pendaftaran Diterima',
            'pra_pendaftaran_ditolak' => 'Pra-Pendaftaran Ditolak',
            'pra_pendaftaran_diperbarui' => 'Pra-Pendaftaran Diperbarui',
            'pra_pendaftaran_dihapus' => 'Pra-Pendaftaran Dihapus',
            'admin_view_detail' => 'Admin Melihat Detail',
            'admin_rejection_reason' => 'Admin Mengisi Alasan Penolakan',
            'public_pra_status_search_success' => 'Pencarian Status Publik (Berhasil)',
            'public_pra_status_search_failed' => 'Pencarian Status Publik (Gagal)',
            'notification_dibuat' => 'Notifikasi Pendaftaran Terkirim',
            'notification_diterima' => 'Notifikasi Diterima Terkirim',
            'notification_ditolak' => 'Notifikasi Ditolak Terkirim',
            
            // Pendaftaran Sertifikasi Events
            'pendaftaran_dibuat' => 'Pendaftaran Dibuat',
            'pendaftaran_created_from_pra' => 'Pendaftaran Dibuat dari Pra-Pendaftaran',
            'pendaftaran_creation_failed' => 'Pendaftaran Gagal Dibuat',
            'pendaftaran_diverifikasi' => 'Pendaftaran Diverifikasi',
            'pendaftaran_ditolak' => 'Pendaftaran Ditolak',
            'audit_pdf_generated_pendaftaran' => 'Audit PDF Pendaftaran Diunduh',
            
            // Asesmen Events
            'asesmen_dimulai' => 'Asesmen Dimulai',
            'asesmen_disimpan' => 'Asesmen Disimpan',
            'asesmen_selesai' => 'Asesmen Selesai',
            'audit_pdf_generated_asesmen' => 'Audit PDF Asesmen Diunduh',
            
            // Keputusan Events
            'keputusan_ditetapkan' => 'Keputusan Ditetapkan',
            'keputusan_dikunci' => 'Keputusan Dikunci (Final)',
            'audit_pdf_generated_keputusan_sertifikat' => 'Audit PDF Keputusan & Sertifikat Diunduh',
            
            // Sertifikat Events
            'sertifikat_diterbitkan' => 'Sertifikat Diterbitkan',
            'sertifikat_dicabut' => 'Sertifikat Dicabut',
            'verifikasi_publik_diakses' => 'Verifikasi Publik Diakses',
        ];
    }

    /**
     * Get event label from metadata.
     */
    public function getEventLabelAttribute(): ?string
    {
        $event = $this->metadata['event'] ?? null;
        if (!$event) {
            return null;
        }
        
        $events = self::getEventTypes();
        return $events[$event] ?? ucwords(str_replace('_', ' ', $event));
    }

    /**
     * Get formatted created_at for display.
     */
    public function getFormattedDateAttribute(): string
    {
        return $this->created_at->translatedFormat('d M Y, H:i:s');
    }

    /**
     * Get time ago for display.
     */
    public function getTimeAgoAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }
}
