<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditService
{
    /**
     * Record an audit log entry.
     *
     * @param string $module Module name (e.g., 'order', 'product', 'auth')
     * @param string $action Action performed (e.g., 'create', 'update', 'complete', 'void', 'cancel')
     * @param Model|null $reference The related model instance
     * @param array|null $oldValues Previous values before change
     * @param array|null $newValues New values after change
     * @param string|null $description Additional description
     * @return AuditLog
     */
    public static function log(
        string $module,
        string $action,
        ?Model $reference = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $description = null
    ): AuditLog {
        return AuditLog::create([
            'user_id' => Auth::id(),
            'module' => $module,
            'action' => $action,
            'reference_type' => $reference ? get_class($reference) : null,
            'reference_id' => $reference?->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'description' => $description,
        ]);
    }
}
