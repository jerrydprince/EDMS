<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    /**
     * The files that belong to the Payment
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function files()
    {
        return $this->belongsToMany(FinanceFiles::class, 'finance_files_payments', 'payment_id', 'finance_file_id');
    }


    /**
     * Get the recipient that owns the Payment
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function recipient()
    {
        return $this->belongsTo(User::class)->select('id', 'first_name', 'last_name', 'email', 'phone','department_id', 'division_id', 'unit_id');
    }
    
    /**
     * Get all of the assignedTo for the Payment
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function assignedTo()
    {
        return $this->hasMany(PaymentAssignment::class, 'payment_id');
    }

    /**
     * Get all of the assignedTo for the Payment
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function assignedBy()
    {
        return $this->hasOne(PaymentAssignment::class, 'payment_id');
    }

    /**
     * Get the ledger associated with the Payment
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function ledger()
    {
        return $this->hasOne(Ledger::class, 'payment_id');
    }

    /**
     * Get the voucher associated with the Payment
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function voucher()
    {
        return $this->hasMany(Voucher::class, 'payment_id');
    }

    /**
     * Get all of the vatVoucher for the Payment
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function vatVoucher()
    {
        return $this->hasMany(VATVoucher::class, 'payment_id');
    }

    /**
     * Get all of the comments for the Payment
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments()
    {
        return $this->hasMany(PaymentComment::class, 'payment_id');
    }

    /**
     * Get all of the comments for the Payment from top to down
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function TopToDownComment()
    {
        return $this->hasMany(PaymentComment::class, 'payment_id');
    }

     /**
     * Get all of the comments for the Payment from down to top
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function DownToTopComment()
    {
        return $this->hasMany(PaymentComment::class, 'payment_id');
    }

    /**
     * Get all of the Receipt for the Payment
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function Receipt()
    {
        return $this->hasMany(PaymentReceipt::class, 'payment_id');
    }

    /**
     * Get all of the parentComments for the Payment
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function parentComments()
    {
        return $this->hasMany(PaymentComment::class, 'payment_id');
    }

    /**
     * Get all of the myComment for the Payment
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function myComments()
    {
        return $this->hasMany(PaymentComment::class, 'payment_id');
    }


    public function getTopToDownCommentsAttribute()
{
    return $this->hasMany(PaymentComment::class)
        ->with('sender')
        ->whereExists(function($query) {
            $query->select(DB::raw(1))
                ->from('payment_comments as pc')
                ->where('pc.recipient_id', $this->assigned_by)
                ->whereColumn('pc.sender_id', 'payment_comments.sender_id');
        })
        ->latest()
        ->get();
}

public function getDownToTopCommentsAttribute()
{
    return $this->hasMany(PaymentComment::class)
        ->with('sender')
        ->where(function($q) {
            $q->where('sender_id', $this->assigned_by)
              ->orWhere('recipient_id', $this->assigned_by);
        })
        ->latest()
        ->get();
}
}
