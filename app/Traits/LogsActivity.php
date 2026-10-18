<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait LogsActivity
{
    protected static function bootLogsActivity()
    {
        static::created(function (Model $model) {
            static::logActivity($model, 'create');
        });

        static::updated(function (Model $model) {
            // Check if status changed (specific to your workflow)
            if ($model->wasChanged('status')) {
                $status = $model->status;
                $action = match ($status) {
                    'prepared' => 'submit',
                    'reviewed' => 'validate',
                    'noted' => 'validate',
                    'draft' => 'revision',
                    default => 'update',
                };
                static::logActivity($model, $action);
            } else {
                static::logActivity($model, 'update');
            }
        });

        static::deleted(function (Model $model) {
            static::logActivity($model, 'delete');
        });
    }

    protected static function logActivity(Model $model, string $action)
    {
        if (!Auth::check()) {
            return;
        }

        $details = [];
        
        // Add context for workflow actions
        if (in_array($action, ['submit', 'validate', 'revision'])) {
            $details['status'] = ucfirst($model->status);
            $details['msg'] = match($action) {
                'submit' => 'Record submitted for review.',
                'validate' => 'Record officially validated/noted.',
                'revision' => 'Record returned to draft for revision.',
                default => '',
            };
            
            if ($action === 'revision' && !empty($model->return_remarks)) {
                $details['remarks'] = $model->return_remarks;
            }
        }

        if ($action === 'update') {
            $details['changes'] = array_intersect_key(
                $model->getChanges(),
                array_flip($model->getFillable())
            );
            // Remove sensitive or bulky fields
            unset($details['changes']['password'], $details['changes']['remember_token']);
            
            if (empty($details['changes'])) {
                $details['msg'] = 'No significant fields changed.';
            }
        }

        if ($action === 'create') {
            $details['msg'] = 'New record created.';
        }

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'model_name' => get_class($model),
            'object_id' => $model->getKey(),
            'details' => $details,
            'ip_address' => request()->ip(),
        ]);
    }
}
