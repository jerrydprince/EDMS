<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Rating;
use App\Models\User;
use App\Models\UserDescription;

class RatingsController extends Controller
{
    public function rateUser(Request $request){
        Rating::insert($request->form);

        $user = User::find($request->user_id);
        $ud = new UserDescription();
        $ud->user_id = $user->id;
        $ud->description = $request->description;
        $ud->save();

        return redirect()->back();
    }
}
