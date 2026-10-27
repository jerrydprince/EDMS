<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use HasFactory;

    protected $table = 'vouchers';

    protected $fillable = [
        'deptal_no',
        'source',
        'date',
        'head',
        'checked_and_passed',
        'checking_officer_id',
        'checked_at',
        'audit_officer_id',
        'audited_at',
        'checking_stamp_path',
        'audit_stamp_path',
        'disbursement_platform',
        'payment_reference',
        'amount',
        'classification_code',
        'voucher_number',
        's_head',
        'address',
        'data_type',
        'signature',
        'payment_id',
        'type',
        'voucher_type',
        'path'
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
        'checked_at' => 'datetime',
        'audited_at' => 'datetime'
    ];

    protected $appends = [
        'has_checking_stamp',
        'has_audit_stamp'
    ];

    public function getHasCheckingStampAttribute(): bool
    {
        return !empty($this->checking_stamp_path);
    }

    public function getHasAuditStampAttribute(): bool
    {
        return !empty($this->audit_stamp_path);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }

    public function checkingOfficer()
    {
        return $this->belongsTo(User::class, 'checking_officer_id');
    }

    public function auditOfficer()
    {
        return $this->belongsTo(User::class, 'audit_officer_id');
    }
}
