<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FileMovementCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_id',
        'file_no',
        'subject',
        'from_office',
        'to_office',
        'releasing_officer_id',
        'receiving_officer_id',
        'date_time_out',
        'date_time_in',
        'is_treated',
        'treated_at',
        'holding_hours',
        'sla_breached',
        'remarks',
    ];

    protected $casts = [
        'date_time_out' => 'datetime',
        'date_time_in' => 'datetime',
        'treated_at' => 'datetime',
        'is_treated' => 'boolean',
        'sla_breached' => 'boolean',
    ];

    public function file()
    {
        return $this->belongsTo(File::class);
    }

    public function releasingOfficer()
    {
        return $this->belongsTo(User::class, 'releasing_officer_id');
    }

    public function receivingOfficer()
    {
        return $this->belongsTo(User::class, 'receiving_officer_id');
    }
}
