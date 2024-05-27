<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WorkFlowController extends Controller
{
    public function index(){
        return inertia('Workflow/Workflows');
    }
}
