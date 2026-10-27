<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StaffQuery extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'query_ref',
        'issued_by',
        'issued_to',
        'subject',
        'incident_category',
        'facts_description',
        'evidence_path',
        'deadline_hours',
        'deadline_at',
        'response_text',
        'response_evidence_path',
        'responded_at',
        'verdict',
        'verdict_comment',
        'verdict_by',
        'verdict_at',
    ];

    protected $casts = [
        'deadline_at' => 'datetime',
        'responded_at' => 'datetime',
        'verdict_at' => 'datetime',
    ];

    public function issuer()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function recipient()
    {
        return $this->belongsTo(User::class, 'issued_to');
    }

    public function adjudicator()
    {
        return $this->belongsTo(User::class, 'verdict_by');
    }
}
