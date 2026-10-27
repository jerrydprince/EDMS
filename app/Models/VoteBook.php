<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoteBook extends Model
{
    use HasFactory;

    protected $table = 'vote_books';

    protected $fillable = [
        'name',
        'account_number',
        'balance',
        'approved_allocation',
        'committed_balance',
        'available_balance',
        'actual_expenditure',
        'gifmis_code',
        'ncoa_code',
        'ergp_code',
        'fund_code',
        'subhead_title',
        'account_type',
        'date_created'
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->date_created)) {
                $model->date_created = now();
            }
            if (empty($model->account_type)) {
                $model->account_type = 'standard';
            }
        });
    }

    protected $casts = [
        'balance' => 'decimal:2',
        'approved_allocation' => 'decimal:2',
        'committed_balance' => 'decimal:2',
        'available_balance' => 'decimal:2',
        'actual_expenditure' => 'decimal:2',
    ];

    /**
     * Encumber / commit funds when a voucher is generated
     */
    public function encumber($amount)
    {
        $this->committed_balance = ($this->committed_balance ?? 0) + $amount;
        $this->available_balance = max(0, ($this->available_balance ?? $this->approved_allocation) - $amount);
        $this->save();
    }

    /**
     * Release commitment and register actual expenditure upon CPO cash disbursement
     */
    public function liquidateExpenditure($amount)
    {
        $this->committed_balance = max(0, ($this->committed_balance ?? 0) - $amount);
        $this->actual_expenditure = ($this->actual_expenditure ?? 0) + $amount;
        $this->balance = max(0, ($this->balance ?? 0) - $amount);
        $this->save();
    }
}
