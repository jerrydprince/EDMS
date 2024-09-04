<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CorrespondenseController extends Controller
{
    public function correspondense(){
        return inertia('correspondence/index');
    }
}
