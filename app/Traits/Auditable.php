<?php

namespace App\Traits;

use App\Models\AuditLog;

/**
 * Trait Auditable
 * 
 * Tambahkan trait ini ke model yang perlu di-audit.
 * Akan otomatis mencatat create, update, delete.
 */
trait Auditable
{
    /**
     * Boot the auditable trait.
     */
    public static function bootAuditable()
    {
        // Log when model is created
        static::created(function ($model) {
            $model->logAudit(AuditLog::ACTION_CREATE, 'Data baru dibuat');
        });

        // Log when model is updated
        static::updated(function ($model) {
            // Only log if there are actual changes
            $changes = $model->getChanges();
            if (!empty($changes)) {
                $model->logAudit(
                    AuditLog::ACTION_UPDATE, 
                    'Data diperbarui',
                    $model->getOriginal(),
                    $changes
                );
            }
        });

        // Log when model is deleted
        static::deleted(function ($model) {
            $model->logAudit(AuditLog::ACTION_DELETE, 'Data dihapus');
        });
    }

    /**
     * Log an audit entry for this model.
     */
    public function logAudit(
        string $action, 
        string $description, 
        ?array $oldValues = null, 
        ?array $newValues = null,
        ?array $metadata = null
    ): AuditLog {
        return AuditLog::log(
            $action,
            $this->getAuditModule(),
            $this->getAuditDescription($action, $description),
            $this,
            $oldValues,
            $newValues,
            $metadata
        );
    }

    /**
     * Get the module name for this model.
     * Override in model if needed.
     */
    public function getAuditModule(): string
    {
        // Default: convert class name to snake_case module name
        $className = class_basename($this);
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $className));
    }

    /**
     * Get the audit description for this model.
     * Override in model for custom descriptions.
     */
    public function getAuditDescription(string $action, string $defaultDescription): string
    {
        $modelName = class_basename($this);
        $identifier = $this->getAuditIdentifier();
        
        return match($action) {
            AuditLog::ACTION_CREATE => "{$modelName} baru dibuat: {$identifier}",
            AuditLog::ACTION_UPDATE => "{$modelName} diperbarui: {$identifier}",
            AuditLog::ACTION_DELETE => "{$modelName} dihapus: {$identifier}",
            default => "{$defaultDescription}: {$identifier}",
        };
    }

    /**
     * Get the identifier for this model in audit logs.
     * Override in model for custom identifiers.
     */
    public function getAuditIdentifier(): string
    {
        return $this->nomor_pendaftaran 
            ?? $this->nomor_sertifikat 
            ?? $this->kode_skema 
            ?? $this->name 
            ?? $this->email 
            ?? "#{$this->id}";
    }

    /**
     * Get all audit logs for this model.
     */
    public function auditLogs()
    {
        return AuditLog::forModel($this)->orderBy('created_at', 'desc')->get();
    }
}
