<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_path',
        'file_name',
        'file_size',
        'mail_id',
        'draft_id',
    ];

    public function mail()
    {
        return $this->belongsTo(Mail::class);
    }

    public function draft()
    {
        return $this->belongsTo(DraftModel::class);
    }
}
