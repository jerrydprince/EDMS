<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemCapability extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'applicable_tiers' => 'array',
        'sort_order' => 'integer',
    ];

    public function activations()
    {
        return $this->hasMany(EntityCapability::class, 'capability_code', 'code');
    }
}
