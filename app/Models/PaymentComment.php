<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentComment extends Model
{
    use HasFactory;

    /**
     * Get the user associated with the PaymentComment
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function sender()
    {
        return $this->hasOne(User::class,'id', 'sender_id')->select('id', 'first_name', 'last_name', 'email', 'phone','department_id', 'division_id', 'unit_id','profile_image') ;
    }

    /**
     * Get the receiver associated with the PaymentComment
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function receiver()
    {
        return $this->hasOne(User::class,'id', 'recipient_id')->select('id', 'first_name', 'last_name', 'email', 'phone','department_id', 'division_id', 'unit_id', 'profile_image');
    }

    
}
