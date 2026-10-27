<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShareFolder extends Model
{
    use HasFactory;

    protected $fillable = ['sharing_id', 'shared_id', 'folder_id'];

    public function folder()
    {
        return $this->belongsTo(Folder::class, 'folder_id');
    }

    public function sharedWith()
    {
        return $this->belongsTo(User::class, 'shared_id');
    }

    public function sharedBy()
    {
        return $this->belongsTo(User::class, 'sharing_id');
    }
}
