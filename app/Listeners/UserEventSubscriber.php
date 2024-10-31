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
    public function handleUserLogout(Logout $event): void {}
 
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
