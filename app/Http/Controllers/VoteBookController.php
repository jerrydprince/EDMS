<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class VoteBookController extends Controller
{
    public function index()
    {
       return inertia('VoteBook/VoteBook');
    }
}
