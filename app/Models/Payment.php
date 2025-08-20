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

}
