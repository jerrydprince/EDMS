<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function archive()
    {
        return $this->hasMany(Archive::class, 'section_id');
    }

    public function documentType()
    {
        return $this->belongsToMany(DocumentType::class, 'section_document_types', 'section_id', 'document_type_id');
    }

    public function fileType()
    {
        return $this->belongsToMany(FileType::class, 'section_file_type', 'section_id', 'file_type_id');
    }
}
