<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServicomReport extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tracking_code',
        'complainant_id',
        'is_anonymous',
        'accused_user_id',
        'category',
        'complaint_details',
        'evidence_file_path',
        'status',
        'assigned_officer_id',
    ];

    protected $casts = [
        'is_anonymous' => 'boolean',
    ];

    public function complainant()
    {
        return $this->belongsTo(User::class, 'complainant_id');
    }

    public function accused()
    {
        return $this->belongsTo(User::class, 'accused_user_id');
    }

    public function assignedOfficer()
    {
        return $this->belongsTo(User::class, 'assigned_officer_id');
    }

    public function panel()
    {
        return $this->hasOne(ServicomPanel::class, 'report_id');
    }
}
