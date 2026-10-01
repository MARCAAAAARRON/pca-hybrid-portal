<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Global scope that filters records by the authenticated user's field site.
 * Applied to field-data models so supervisors only see their own site's data.
 */
class FieldSiteScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $user = auth()->user();

        if ($user && ($user->isSupervisor() || $user->isSubSupervisor()) && $user->field_site_id) {
            $builder->where($model->getTable() . '.field_site_id', $user->field_site_id);
        }
    }
}
