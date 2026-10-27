<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ConfidentialScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (auth()->check()) {
            $user = auth()->user();
            
            // Super users, AGF, DA, DD APD, AD Appointment, AD Promotion, AD Pool
            if (method_exists($user, 'hasRole') && (
                $user->hasRole('super admin') || 
                $user->hasRole('super user') || 
                $user->hasRole('Accountant-General') ||
                ($user->department_id == 14 && ($user->hasRole('director') || $user->hasRole('assistant director'))) ||
                (method_exists($user, 'can') && ($user->can('pool view') || $user->can('confidential view')))
            )) {
                return;
            }

            // Also allow the file creator
            $builder->where(function($query) use ($model, $user) {
                $query->where($model->getTable() . '.is_confidential', false)
                      ->orWhere($model->getTable() . '.created_by', $user->id);
            });
        } else {
            // Unauthenticated users cannot see confidential files
            $builder->where($model->getTable() . '.is_confidential', false);
        }
    }
}
