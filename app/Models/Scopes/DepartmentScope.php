<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class DepartmentScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Only apply if there is an authenticated user
        if (auth()->check()) {
            $user = auth()->user();
            
            // Exempt super admins, super users, admins, and Accountant-General role from the scope
            // We use method_exists just in case HasRoles trait isn't booted properly yet
            if (method_exists($user, 'hasRole') && ($user->hasRole('super admin') || $user->hasRole('super user') || $user->hasRole('admin') || $user->hasRole('Accountant-General') || $user->hasPermissionTo('department view'))) {
                return;
            }

            // Apply department scoping
            if ($user->department_id) {
                $builder->where($model->getTable() . '.department_id', $user->department_id);
            } else {
                // If the user has no department, they can only see records with no department
                $builder->whereNull($model->getTable() . '.department_id'); 
            }
        }
    }
}
