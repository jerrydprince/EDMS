<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organisation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_name',
        'email',
        'phone',
        'address',
        'website',
        'rc_number',
        'tin_number',
        'motto',
        'logo',
        'primary_color',
        'secondary_color',
        'timezone',
        'currency_symbol',
        'currency_code',
        'stamp_path',
    ];
}
