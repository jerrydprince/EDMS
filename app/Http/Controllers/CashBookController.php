<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CashBookController extends Controller
{
    public function index(){
        return inertia('CashBook/CashBook');
    }
}
