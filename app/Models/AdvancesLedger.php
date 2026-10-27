<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdvancesLedger extends Model
{
    use HasFactory;

    protected $table = 'advances_ledgers';

    protected $fillable = [
        'payment_id',
        'beneficiary_user_id',
        'advance_type',
        'amount_advanced',
        'purpose',
        'destination',
        'journey_start_date',
        'journey_end_date',
        'due_retirement_date',
        'status',
        'amount_retired',
        'refund_amount',
        'refund_rrr',
        'retired_at',
        'created_by'
    ];

    protected $casts = [
        'amount_advanced' => 'decimal:2',
        'amount_retired' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'journey_start_date' => 'date',
        'journey_end_date' => 'date',
        'due_retirement_date' => 'date',
        'retired_at' => 'datetime'
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }

    public function beneficiary()
    {
        return $this->belongsTo(User::class, 'beneficiary_user_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function retirements()
    {
        return $this->hasMany(AdvancesRetirement::class, 'advances_ledger_id');
    }
}
