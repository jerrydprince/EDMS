<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    use HasFactory;
    protected $fillable = [
        'name', 'file_type', 'status', 'subject', 'is_incoming',
        'is_outgoing','department_id', 'doc_no', 'sent_from_department_id', 'sent_to_department_id',
        'sent_from_division_id', 'sent_from_unit_id', 'division_id', 'unit_id',
    ];
    public function movements()
    {
        return $this->hasMany(FileMovement::class);
    }
    public function department()
    {
        return $this->belongsTo(Department::class);
    }
    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
    public function sentFromDepartment()
    {
        return $this->belongsTo(Department::class, 'sent_from_department_id');
    }

    public function sentToDepartment()
    {
        return $this->belongsTo(Department::class, 'sent_to_department_id');
    }
    public function sentFromDivision()
    {
        return $this->belongsTo(Division::class, 'sent_from_division_id');
    }

    public function sentFromUnit()
    {
        return $this->belongsTo(Unit::class, 'sent_from_unit_id');
    }
}
