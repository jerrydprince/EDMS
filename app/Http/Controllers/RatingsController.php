<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Rating;
use App\Models\User;
use App\Models\UserDescription;

class RatingsController extends Controller
{
    public function rateUser(Request $request){
        if ($request->has('form') && is_array($request->form) && count($request->form) > 0) {
            Rating::insert($request->form);
        }

        if ($request->user_id && $request->description) {
            $user = User::find($request->user_id);
            if ($user) {
                $ud = new UserDescription();
                $ud->user_id = $user->id;
                $ud->description = $request->description;
                $ud->save();
            }
        }

        return redirect()->back();
    }
}
