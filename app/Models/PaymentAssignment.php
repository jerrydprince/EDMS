<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentAssignment extends Model
{
    use HasFactory;

    protected $table = 'payment_assignment';

    /**
     * Get the user associated with the PaymentAssignment
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function user()
    {
        return $this->hasOne(User::class, 'id', 'recipient_id');
    }

//     public function comments()
// {
//     return $this->hasMany(PaymentComment::class, 'assignment_id');
// }

    /**
     * Get all of the comments for the PaymentAssignment
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments()
    {
        return $this->hasMany(PaymentComment::class, 'payment_assignment_id');
    }
}
