<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentAuditQuery extends Model
{
    use HasFactory;

    protected $table = 'payment_audit_queries';

    protected $fillable = [
        'payment_id',
        'queried_by',
        'queried_to_section',
        'query_subject',
        'query_details',
        'response_details',
        'responded_by',
        'responded_at',
        'status'
    ];

    protected $casts = [
        'responded_at' => 'datetime'
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }

    public function auditor()
    {
        return $this->belongsTo(User::class, 'queried_by');
    }

    public function respondent()
    {
        return $this->belongsTo(User::class, 'responded_by');
    }
}
