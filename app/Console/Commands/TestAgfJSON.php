<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AdminController;
use Illuminate\Http\Request;

class TestAgfJSON extends Command {
    protected $signature = 'test:agfjson';
    public function handle() {
        $user = User::where('email', 'agf@oagf.gov.ng')->first();
        Auth::login($user);
        $request = Request::create('/dashboard', 'GET');
        $request->headers->set('X-Inertia', 'true');
        app()->instance('request', $request);
        
        $controller = new AdminController();
        $response = $controller->index();
        echo json_encode($response->toResponse($request)->getContent());
    }
}
