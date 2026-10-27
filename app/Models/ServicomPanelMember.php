<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServicomPanelMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'panel_id',
        'user_id',
    ];

    public function panel()
    {
        return $this->belongsTo(ServicomPanel::class, 'panel_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
