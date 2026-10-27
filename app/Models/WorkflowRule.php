<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkflowRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'workflow_type',
        'department_id',
        'rule_key',
        'rule_value',
        'description'
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
