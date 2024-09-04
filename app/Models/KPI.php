<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KPI extends Model
{
    use HasFactory;

    protected $table = "kpis";

    /**
     * Get the kra that owns the kpi
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function kra()
    {
        return $this->belongsTo(KRA::class);
    }
}
