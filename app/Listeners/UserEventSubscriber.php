<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Events\Dispatcher;
use MakiDizajnerica\GeoLocation\Facades\GeoLocation;
use Jenssegers\Agent\Agent;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\User;
use App\Models\LoginActivity;

class UserEventSubscriber
{
    /**
     * Handle user login events.
     */
    public function handleUserLogin(Login $event) {
        $user = $event->user ?? auth()->user();
        if (!$user) {
            return;
        }

        $request = request();
        if (!$user->hasRole('super admin')) {
            if (!$user->is_approved) {
                Auth::logout();
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'email' => 'Please wait while we approve your account',
                ]);
            }
        }

        // take the attendance
        Attendance::updateOrCreate(
            ['user_id' => $user->id, 'date' => now()->toDateString()],
            ['login_time' => Attendance::where('user_id', $user->id)
                        ->where('date', now()->toDateString())
                        ->value('login_time') ?? now()]
        );

        // user session
        $location = 'Unknown';
        try {
            $collection = GeoLocation::lookup($request->ip());
            $country = $collection ? $collection->get('country') : null;
            $city = $collection ? $collection->get('city') : null;
            
            if (!empty($country) && !empty($city)) {
                $location = "$country, $city";
            } elseif (!empty($country)) {
                $location = $country;
            } elseif (!empty($city)) {
                $location = $city;
            }
        } catch (\Throwable $e) {
            // Ignore geolocation error
        }

        $agent = new Agent();
        $browser = $agent->browser() ?: 'unknown';

        if ($request->hasSession()) {
            $request->session()->put('browser', $browser);
            $request->session()->put('location', $location);
            if ($branch = $this->userBranchDetails($user)) {
                $request->session()->put('branch', $branch);
            }
        }

        LoginActivity::create([
            'user_id' => $user->id,
            'name' => $user->first_name . ' ' . $user->last_name,
            'ip_address' => request()->ip(),
            'browser' => $browser,
            'location' => $location
        ]);

        // adds to the forensic activity log
        \App\Services\AuditTrailService::logSecurity(
            'login',
            $user->first_name . ' ' . $user->last_name . " authenticated into the system",
            [
                'event' => 'login',
                'browser' => $browser,
                'location' => $location,
                'department' => $user->department?->title ?? null,
                'division' => $user->division?->title ?? null,
                'unit' => $user->unit?->title ?? null
            ],
            $user,
            \App\Services\AuditTrailService::SEVERITY_ROUTINE
        );
    }

    public function getDevice(){
        $agent = new Agent();

        $isMobile = $agent->isMobile();   // Returns true if the device is a mobile
        $isTablet = $agent->isTablet();   // Returns true if the device is a tablet
        $isDesktop = $agent->isDesktop(); // Returns true if the device is a desktop
        $browser = $agent->browser();     // e.g., Chrome, Safari
        $platform = $agent->platform();   // e.g., iOS, Android, Windows

        $device = '';

        // Example usage
        if ($isMobile) {
            $device = 'Mobile';
        } elseif ($isTablet) {
            $device = 'Tablet';
        } else {
            $device = 'Desktop';
        }
        return $device;
    }

    public function userBranchDetails($user = null){
        $user = $user ?? auth()->user();
        if (!$user) return null;

        if ($user->unit_id && $user->unit) {
            return [
                'branch' => 'unit',
                'branch_id' => $user->unit_id,
                'branch_name' => $user->unit->title
            ];
        }

        if ($user->division_id && $user->division) {
            return [
                'branch' => 'division',
                'branch_id' => $user->division_id,
                'branch_name' => $user->division->title
            ];
        }

        if ($user->department_id && $user->department) {
            return [
                'branch' => 'department',
                'branch_id' => $user->department_id,
                'branch_name' => $user->department->title
            ];
        }
        return null;
    }
 
    /**
     * Handle user logout events.
     */
    public function handleUserLogout(Logout $event): void {
        $user = $event->user ?? auth()->user();
        if (!$user) return;

        \App\Services\AuditTrailService::logSecurity(
            'logout',
            $user->first_name . ' ' . $user->last_name . " terminated session / logged out",
            [
                'event' => 'logout',
                'department' => $user->department?->title ?? null,
                'division' => $user->division?->title ?? null,
                'unit' => $user->unit?->title ?? null
            ],
            $user,
            \App\Services\AuditTrailService::SEVERITY_ROUTINE
        );
    }
 
    /**
     * Register the listeners for the subscriber.
     */
    public function subscribe(Dispatcher $events): void
    {
        $events->listen(
            Login::class,
            [UserEventSubscriber::class, 'handleUserLogin']
        );
 
        $events->listen(
            Logout::class,
            [UserEventSubscriber::class, 'handleUserLogout']
        );
    }
}
