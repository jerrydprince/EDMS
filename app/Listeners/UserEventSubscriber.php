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

class UserEventSubscriber
{
      /**
     * Handle user login events.
     */
    public function handleUserLogin(Login $event) {
        $request = request();
        if(!auth()->user()->hasRole('super admin')){
            if(!auth()->user()->is_approved){
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Please wait while we approve your account',
                ])->onlyInput('email');
            }
        }

        //take the attendance
        Attendance::updateOrCreate(
            ['user_id' => auth()->user()->id, 'date'=>now()->toDateString()],
            ['login_time' => Attendance::where('user_id', auth()->user()->id)
                        ->where('date', now()->toDateString())
                        ->value('login_time') ?? now()]
        );

        //user session
        $collection = GeoLocation::lookup($request->ip());
        $location = $collection? $collection->get('country').", ".$collection->get('city'): null;

        $agent = new Agent();
        $browser = $agent->browser();
        $request->session()->put('browser', $browser);

        session(['branch'=>$this->userBranchDetails($request)]);
      
         $browser = $agent->browser()? $agent->browser(): 'unknown';
         session()->put(['browser'=>$browser, 'location' => $location]);

          // adds to the activity log
          $user = new User();
          activity()
          ->performedOn($user)
          ->causedBy(auth()->user()->id)
          ->withProperties([
              'archive' => '',
              'ip' => request()->ip(),
              'user' => auth()->user()->first_name.' '.auth()->user()->last_name,
              'user_agent' => request()->userAgent(),
              'event' => 'login',
              'device' => $this->getDevice(),
              'document_type' =>[],
              'files' => [],
              'department' =>auth()->user()->department,
                'division' => auth()->user()->division,
                'unit' => auth()->user()->unit
              ])
          ->log(auth()->user()->first_name.' '.auth()->user()->last_name." logged in");
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

    public function userBranchDetails($request){
        if(auth()->check()){
            if(auth()->user()->unit_id){
             return [
                    'branch' => 'unit',
                    'branch_id' => auth()->user()->id,
                    'branch_name' =>auth()->user()->unit->title
             ];
            }

            if(auth()->user()->division_id){
                return [
                    'branch' => 'division',
                    'branch_id' => auth()->user()->id,
                    'branch_name' =>auth()->user()->division->title
                ];
            }

            if(auth()->user()->department_id){
                return [
                    'branch' => 'department',
                    'branch_id' => auth()->user()->id,
                    'branch_name' =>auth()->user()->department->title
                ];
            }
        }
        
    }
 
    /**
     * Handle user logout events.
     */
    public function handleUserLogout(Logout $event): void {

        $user = new User();
        activity()
            ->performedOn($user)
            ->causedBy(auth()->user()->id)
            ->withProperties([
                'archive' => '',
                'ip' => request()->ip(),
                'user' => auth()->user()->first_name.' '.auth()->user()->last_name,
                'user_agent' => request()->userAgent(),
                'event' => 'logout',
                'device' => $this->getDevice(),
                'document_type' =>[],
                'files' => [],
                'department' =>auth()->user()->department,
                'division' => auth()->user()->division,
                'unit' => auth()->user()->unit
                ])
            ->log(auth()->user()->first_name.' '.auth()->user()->last_name." logged out");
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
