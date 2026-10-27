<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Models\Department;
use App\Models\User;
use Auth;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Defines the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function share(Request $request): array
    {
        $user = auth()->user();

        // Ensure we eager load relationships instead of lazy loading them below
        if ($user) {
            $user->loadMissing(['department', 'division', 'unit', 'section']);
        }

        return array_merge(parent::share($request), [
            'shared_departments' => fn () => \Illuminate\Support\Facades\Cache::remember("shared_departments_user_" . ($user ? $user->id : 0), 3600, fn () => Department::get()),
            'base' => fn () => url('/'),
            'userBranch' => fn () => $user ? $user : [],
            'user' => fn () => $user ? $user : '',
            'branch' => fn() => $user ? 
                ($user->section_id ? 'section' :
                ($user->unit_id ? 'unit' :
                ($user->division_id ? 'division' :
                'department'))) :
                [],
            'branchName' => fn() => $user ? 
                ($user->section_id ? 'section' :
                ($user->unit_id ? 'unit' :
                ($user->division_id ? 'division' :
                'department'))) :
                [],
            'roles' => fn () => $user ? \Illuminate\Support\Facades\Cache::remember("user_{$user->id}_roles", 300, fn() => $user->getRoleNames()) : [],
            'userDepartment' => fn () => $user ? $user->department : null,
            'user_roles' => fn () => $user ? \Illuminate\Support\Facades\Cache::remember("user_{$user->id}_roles", 300, fn() => $user->getRoleNames()) : [],
            'notifications' => fn () => $user ? \Illuminate\Support\Facades\Cache::remember("user_{$user->id}_notifs", 15, fn() => $user->notifications()->take(10)->get()) : [],
            'unreadNotifications' => fn () => $user ? \Illuminate\Support\Facades\Cache::remember("user_{$user->id}_unread_notifs", 15, fn() => $user->unreadNotifications()->take(10)->get()) : [],
            'total_task_notifications' => fn () => $user ? \Illuminate\Support\Facades\Cache::remember("user_{$user->id}_unread_count", 15, fn() => $user->unreadNotifications()->count()) : 0,
            'pending_workflow_count' => fn () => $user ? \Illuminate\Support\Facades\Cache::remember("user_{$user->id}_pending_wf_count", 15, function() use ($user) {
                return \App\Models\TaskAssignment::where('assigned_to', $user->id)
                    ->where('status', '!=', 'completed')
                    ->whereHas('task', fn($q) => $q->whereNotIn('status', ['completed', 'archived']))
                    ->count();
            }) : 0,
            'flash' => fn () => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
                'warning' => $request->session()->get('warning'),
                'info' => $request->session()->get('info'),
                'message' => $request->session()->get('message'),
            ],
            'errors' => fn () => $request->session()->get('errors')
                ? $request->session()->get('errors')->getBag('default')->getMessages()
                : (object)[],
            'permissions' => fn () => $user 
                ? \Illuminate\Support\Facades\Cache::remember("user_{$user->id}_effective_permissions", 60, fn() => \App\Services\PermissionRegistryService::getEffectiveUserPermissions($user)) 
                : [],
            'activeCapabilities' => fn () => $user 
                ? \Illuminate\Support\Facades\Cache::remember("user_{$user->id}_active_capabilities", 60, fn() => \App\Services\PermissionRegistryService::getActiveCapabilitiesForUser($user)) 
                : [],
        ]);
    }
}
