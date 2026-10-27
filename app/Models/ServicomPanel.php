<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServicomPanel extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_id',
        'panel_title',
        'chairperson_id',
        'secretary_id',
        'hearing_date',
        'findings',
        'recommendations',
        'action_taken',
        'status',
    ];

    protected $casts = [
        'hearing_date' => 'date',
    ];

    public function report()
    {
        return $this->belongsTo(ServicomReport::class, 'report_id');
    }

    public function chairperson()
    {
        return $this->belongsTo(User::class, 'chairperson_id');
    }

    public function secretary()
    {
        return $this->belongsTo(User::class, 'secretary_id');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'servicom_panel_members', 'panel_id', 'user_id')->withTimestamps();
    }
}
