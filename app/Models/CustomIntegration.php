<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomIntegration extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'logo',
        'description',
        'webhook_url',
        'api_key'
    ];
}
