<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Request;

class TestAgfDashboard extends Command
{
    protected $signature = 'test:agf-dashboard';
    protected $description = 'Test AGF Dashboard';

    public function handle()
    {
        $user = \App\Models\User::where('email', 'agf@oagf.gov.ng')->first();
        auth()->login($user);
        
        $kernel = app()->make(\Illuminate\Contracts\Http\Kernel::class);
        
        $request = Request::create('/dashboard', 'GET');
        $response = $kernel->handle($request);
        if ($response->status() != 200) {
            file_put_contents('dashboard_error.html', $response->getContent());
        } else {
            file_put_contents('dashboard_success.html', $response->getContent());
        }
        $this->info("Dashboard Status: " . $response->status());

        $request2 = Request::create('/edms', 'GET');
        $response2 = $kernel->handle($request2);
        if ($response2->status() != 200) {
            file_put_contents('edms_error.html', $response2->getContent());
        } else {
            file_put_contents('edms_success.html', $response2->getContent());
        }
        $this->info("EDMS Status: " . $response2->status());
    }
}
