<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * ============================================================================
 * EvidenceKuk Model - Evidence per KUK for Asesmen
 * ============================================================================
 * 
 * Stores evidence (files/links) per KUK to support the assessment process.
 * All files are stored in private storage for security.
 * 
 * Compliance: ISO 17024, BNSP Pedoman
 * 
 * Relations:
 * - belongsTo Asesmen
 * - belongsTo Kuk
 * - belongsTo User (uploader)
 * 
 * @see ISO 17024:2012 Clause 7.4 (Examination process)
 * ============================================================================
 */
class EvidenceKuk extends Model
{
    use Auditable;

    /**
     * The table associated with the model.
     */
    protected $table = 'evidence_kuk';

    /**
     * Get the audit module name.
     */
    public function getAuditModule(): string
    {
        return 'evidence';
    }

    /**
     * Type constants
     */
    const TYPE_FILE = 'file';
    const TYPE_LINK = 'link';

    /**
     * Allowed mime types for file upload
     */
    const ALLOWED_MIME_TYPES = [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'image/gif',
        'application/zip',
        'application/x-zip-compressed',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ];

    /**
     * Allowed file extensions
     */
    const ALLOWED_EXTENSIONS = ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'zip', 'doc', 'docx', 'xls', 'xlsx'];

    /**
     * Max file size in bytes (10MB)
     */
    const MAX_FILE_SIZE = 10485760;

    /**
     * Storage disk for evidence files
     */
    const STORAGE_DISK = 'local';
    const STORAGE_PATH = 'private/evidence';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'asesmen_id',
        'kuk_id',
        'uploaded_by',
        'type',
        'file_path',
        'file_name_original',
        'file_mime_type',
        'file_size',
        'link_url',
        'description',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'asesmen_id' => 'integer',
        'kuk_id' => 'integer',
        'uploaded_by' => 'integer',
        'file_size' => 'integer',
    ];

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    /**
     * Get the asesmen that owns this evidence.
     */
    public function asesmen(): BelongsTo
    {
        return $this->belongsTo(Asesmen::class, 'asesmen_id');
    }

    /**
     * Get the KUK that this evidence belongs to.
     */
    public function kuk(): BelongsTo
    {
        return $this->belongsTo(Kuk::class, 'kuk_id');
    }

    /**
     * Get the user who uploaded this evidence.
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // =========================================================================
    // ACCESSORS
    // =========================================================================

    /**
     * Get type label.
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            self::TYPE_FILE => 'File',
            self::TYPE_LINK => 'Link',
            default => ucfirst($this->type),
        };
    }

    /**
     * Get type icon.
     */
    public function getTypeIconAttribute(): string
    {
        return match($this->type) {
            self::TYPE_FILE => 'fas fa-file',
            self::TYPE_LINK => 'fas fa-link',
            default => 'fas fa-paperclip',
        };
    }

    /**
     * Get file icon based on mime type.
     */
    public function getFileIconAttribute(): string
    {
        if ($this->type !== self::TYPE_FILE) {
            return 'fas fa-link';
        }

        return match(true) {
            str_contains($this->file_mime_type ?? '', 'pdf') => 'fas fa-file-pdf text-danger',
            str_contains($this->file_mime_type ?? '', 'image') => 'fas fa-file-image text-info',
            str_contains($this->file_mime_type ?? '', 'zip') => 'fas fa-file-archive text-warning',
            str_contains($this->file_mime_type ?? '', 'word') => 'fas fa-file-word text-primary',
            str_contains($this->file_mime_type ?? '', 'excel') || str_contains($this->file_mime_type ?? '', 'spreadsheet') => 'fas fa-file-excel text-success',
            default => 'fas fa-file text-secondary',
        };
    }

    /**
     * Get human readable file size.
     */
    public function getFileSizeHumanAttribute(): string
    {
        if (!$this->file_size) {
            return '-';
        }

        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get display name for evidence.
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->type === self::TYPE_FILE) {
            return $this->file_name_original ?? basename($this->file_path ?? 'Unknown');
        }

        return $this->link_url ?? 'Unknown Link';
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    /**
     * Scope by asesmen.
     */
    public function scopeByAsesmen($query, $asesmenId)
    {
        return $query->where('asesmen_id', $asesmenId);
    }

    /**
     * Scope by KUK.
     */
    public function scopeByKuk($query, $kukId)
    {
        return $query->where('kuk_id', $kukId);
    }

    /**
     * Scope for files only.
     */
    public function scopeFiles($query)
    {
        return $query->where('type', self::TYPE_FILE);
    }

    /**
     * Scope for links only.
     */
    public function scopeLinks($query)
    {
        return $query->where('type', self::TYPE_LINK);
    }

    // =========================================================================
    // METHODS
    // =========================================================================

    /**
     * Check if evidence is a file.
     */
    public function isFile(): bool
    {
        return $this->type === self::TYPE_FILE;
    }

    /**
     * Check if evidence is a link.
     */
    public function isLink(): bool
    {
        return $this->type === self::TYPE_LINK;
    }

    /**
     * Check if file exists in storage.
     */
    public function fileExists(): bool
    {
        if (!$this->isFile() || !$this->file_path) {
            return false;
        }

        return Storage::disk(self::STORAGE_DISK)->exists($this->file_path);
    }

    /**
     * Get full file path for download.
     */
    public function getFullPath(): ?string
    {
        if (!$this->file_path) {
            return null;
        }

        return Storage::disk(self::STORAGE_DISK)->path($this->file_path);
    }

    /**
     * Delete file from storage when model is deleted.
     */
    protected static function booted(): void
    {
        static::deleting(function (EvidenceKuk $evidence) {
            if ($evidence->isFile() && $evidence->file_path) {
                Storage::disk(self::STORAGE_DISK)->delete($evidence->file_path);
            }
        });
    }

    /**
     * Get allowed extensions as string.
     */
    public static function getAllowedExtensionsString(): string
    {
        return implode(', ', array_map(fn($ext) => strtoupper($ext), self::ALLOWED_EXTENSIONS));
    }

    /**
     * Get max file size in MB.
     */
    public static function getMaxFileSizeMB(): float
    {
        return self::MAX_FILE_SIZE / 1048576;
    }
}
