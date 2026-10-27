<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskDocumentVersion extends Model
{
    use HasFactory;

    protected $table = 'task_document_versions';

    protected $fillable = [
        'task_id',
        'version',
        'path',
        'created_by',
        'action',
        'note',
    ];

    protected $casts = [
        'version' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function annotations()
    {
        return $this->hasMany(TaskAnnotation::class, 'version_id');
    }
}
