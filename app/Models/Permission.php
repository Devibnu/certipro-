<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'module',
        'action',
        'description',
    ];

    /**
     * Module constants
     */
    const MODULE_DASHBOARD = 'dashboard';
    const MODULE_USERS = 'users';
    const MODULE_CMS = 'cms';
    const MODULE_PRA_PENDAFTARAN = 'pra_pendaftaran';
    const MODULE_PENDAFTARAN = 'pendaftaran_sertifikasi';
    const MODULE_SKEMA = 'skema_sertifikasi';
    const MODULE_UNIT_KOMPETENSI = 'unit_kompetensi';
    const MODULE_KUK = 'kuk';
    const MODULE_ASESMEN = 'asesmen';
    const MODULE_KEPUTUSAN = 'keputusan_sertifikasi';
    const MODULE_SERTIFIKAT = 'sertifikat';
    const MODULE_AUDIT_LOG = 'audit_log';
    const MODULE_SETTINGS = 'settings';

    /**
     * Action constants
     */
    const ACTION_VIEW = 'view';
    const ACTION_CREATE = 'create';
    const ACTION_UPDATE = 'update';
    const ACTION_DELETE = 'delete';
    const ACTION_APPROVE = 'approve';
    const ACTION_ASSIGN = 'assign';
    const ACTION_INPUT = 'input';

    /**
     * The roles that have this permission.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permission')
            ->withTimestamps();
    }

    /**
     * Create permission name from module and action
     */
    public static function makeName(string $module, string $action): string
    {
        return "{$module}.{$action}";
    }

    /**
     * Get or create a permission
     */
    public static function findOrCreateByName(string $name, ?string $displayName = null, ?string $module = null, ?string $action = null): self
    {
        $parts = explode('.', $name);
        $module = $module ?? $parts[0] ?? 'general';
        $action = $action ?? $parts[1] ?? 'access';
        
        return self::firstOrCreate(
            ['name' => $name],
            [
                'display_name' => $displayName ?? ucwords(str_replace(['_', '.'], ' ', $name)),
                'module' => $module,
                'action' => $action,
            ]
        );
    }
}
