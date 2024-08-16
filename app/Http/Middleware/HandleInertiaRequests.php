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
        return array_merge(parent::share($request), [
            'shared_departments' => fn () => Department::get()->take(4),
            'base' => fn () => url('/'),
            // 'department' => fn () => Auth::check()? User::with(['department','division','unit'])->find(auth()->user()->id): [],
            'userBranch' => fn () => Auth::check()? User::with(['department','division','unit'])->find(auth()->user()->id): [],
            'user' => fn () => auth()->user(),
            // 'division' => fn () => Auth::check()? \App\Models\Division::find(auth()->user()->division_id): [],
            // 'unit' => fn () => Auth::check()? \App\Models\Unit::find(auth()->user()->unit_id): [],
            // 'department_div_unit' => fn () =>
            //     $department = Auth::check()?Department::with(['division'=>function($query){
            //         $query->with('unit');
            //     }])->where('id', Auth::user()->department_id)->first() : [],
            'branch' => fn() => (Auth::check()) ? 
                ((auth()->user()->unit_id) ? 'unit' :
                ((auth()->user()->division_id) ? 'division' :
                'department')) :
                [],
            'roles'=>Auth::check()? Auth::user()->roles->pluck('name')->toArray() :[],
            'permissions'=>Auth::check()? Auth::user()->permissions()->pluck('name') :[]
        ]);
    }
}
