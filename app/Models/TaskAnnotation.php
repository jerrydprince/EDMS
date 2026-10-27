<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskAnnotation extends Model
{
    use HasFactory;

    protected $table = 'task_annotations';

    protected $fillable = [
        'version_id',
        'page',
        'x',
        'y',
        'text',
        'resolved_at',
        'created_by',
    ];

    protected $casts = [
        'page' => 'integer',
        'x' => 'float',
        'y' => 'float',
        'resolved_at' => 'datetime',
    ];

    public function version()
    {
        return $this->belongsTo(TaskDocumentVersion::class, 'version_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
