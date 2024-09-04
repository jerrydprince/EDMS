<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KRA extends Model
{
    use HasFactory;

    /**
     * Get all of the kpi for the KRA
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function kpi()
    {
        return $this->hasMany(KPI::class, 'kra_id');
    }
}
