<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PoolPosting extends Model
{
    use HasFactory;

    protected $fillable = [
        'posting_ref',
        'pool_officer_id',
        'pool_requisition_id',
        'from_mda_name',
        'to_mda_name',
        'posting_type',
        'effective_date',
        'approval_authority',
        'approved_by',
        'approved_at',
        'posting_letter_path',
        'assumption_due_date',
        'assumption_status',
        'assumption_slip_returned_at',
        'assumption_slip_path',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'assumption_due_date' => 'date',
        'assumption_slip_returned_at' => 'date',
        'approved_at' => 'datetime',
    ];

    public function poolOfficer()
    {
        return $this->belongsTo(PoolOfficer::class, 'pool_officer_id');
    }

    public function requisition()
    {
        return $this->belongsTo(PoolRequisition::class, 'pool_requisition_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
