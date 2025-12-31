<?php

namespace App\Http\Controllers\Auth;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        $user = $request->user();

        // Redirect based on role
        // if ($user->hasRole('admin')) {
        //     return redirect()->route('admin.dashboard');
        // }

        // if ($user->hasRole('teacher')) {
        //     return redirect()->route('teacher.dashboard');
        // }

        // if ($user->hasRole('student')) {
        //     return redirect()->route('student.dashboard');
        // }

        // if($user->hasPermissionTo('registry view')){
        //     return redirect()->route('registry');
        // }

        // if($user->hasPermissionTo('edms view')){
        //     return redirect()->route('edms');
        // }

        // Default redirect
        return redirect()->route('admin.dashboard');
    }
}
