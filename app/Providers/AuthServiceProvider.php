<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        \App\Models\Archive::class => \App\Policies\ArchivePolicy::class,
        \App\Models\File::class => \App\Policies\FilePolicy::class,
        \App\Models\Task::class => \App\Policies\TaskPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Gate::before(function ($user, $ability) {
            if ($user->hasRole('super admin')) {
                return true;
            }

            // If the permission ability belongs to an EDMS module that is inactive for this user's department, deny access immediately
            if (!\App\Services\PermissionRegistryService::isPermissionAllowedForUser($user, $ability)) {
                return false;
            }

            return null;
        });
    }
}
