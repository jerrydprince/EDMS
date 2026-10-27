<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdvancesRetirement extends Model
{
    use HasFactory;

    protected $table = 'advances_retirements';

    protected $fillable = [
        'advances_ledger_id',
        'retirement_voucher_no',
        'total_expended',
        'refund_amount',
        'reimbursement_amount',
        'evidence_paths',
        'checked_by',
        'audited_by',
        'status'
    ];

    protected $casts = [
        'total_expended' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'reimbursement_amount' => 'decimal:2',
        'evidence_paths' => 'array'
    ];

    public function ledger()
    {
        return $this->belongsTo(AdvancesLedger::class, 'advances_ledger_id');
    }

    public function checkingOfficer()
    {
        return $this->belongsTo(User::class, 'checked_by');
    }

    public function auditOfficer()
    {
        return $this->belongsTo(User::class, 'audited_by');
    }
}
