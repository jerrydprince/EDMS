<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportInfo extends Model
{
    protected $fillable = [
        'email',
        'phone_number',
        'location',
        'start_day',
        'end_day',
        'start_time',
        'end_time',
    ];
}
