<?php

namespace App\Traits;

use App\Models\LogEventosAutos;
use Illuminate\Support\Facades\Auth;

trait AuditorAutos
{
    public static function bootAuditorAutos()
    {
        static::created(function ($model) {
            self::saveAuditLog($model, 'created', [], $model->getAttributes());
        });

        static::updated(function ($model) {
            $changes = $model->getChanges();
            $old = [];
            $new = [];

            foreach ($changes as $field => $value) {
                if ($field === 'updated_at') continue;
                $old[$field] = $model->getOriginal($field);
                $new[$field] = $value;
            }

            if (!empty($old)) {
                $event = (array_key_exists('activo', $old) && $new['activo'] == 0) ? 'logical_deletion' : 'updated';
                self::saveAuditLog($model, $event, $old, $new);
            }
        });

        static::deleted(function ($model) {
            self::saveAuditLog($model, 'deleted', $model->getAttributes(), []);
        });
    }

    protected static function saveAuditLog($model, $event, $oldValues, $newValues)
    {
        LogEventosAutos::create([
            'user_id'    => Auth::id(),
            'table_name' => $model->getTable() ?? 'Modelo no identificado',
            'record_id'  => $model->id ?? null,
            'event'      => $event,
            'old_values' => $oldValues ??  null,
            'new_values' => $newValues ?? null,
            'ip_address' => request()->ip(),
        ]);
    }
}