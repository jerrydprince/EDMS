<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Accounts extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'account_number',
        'balance',
        'currency',
        'status',
        'opening_balance',
        'department',
    ];
}
