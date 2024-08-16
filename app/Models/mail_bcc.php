<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class mail_bcc extends Model
{
    use HasFactory;
    protected $fillable=['recipient_mail', 'mail_id'];

}
