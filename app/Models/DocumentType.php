<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentType extends Model
{
    use HasFactory;

    //returns a belongs to relationship
    public function metadata(){
        return $this->belongsToMany(Metadata::class);
    }

    // public function getFormattedUpdatedAtAttribute()
    // {
    //     return $this->updated_at->diffForHumans();
    // }

    // protected function updatedAt(): Attribute
    // {
    //     return Attribute::make(
    //         set: fn ($value) => $value->diffForHumans(),
    //     );
    // }

    /**
     * The department that belong to the DocumentType
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function department()
    {
        return $this->belongsToMany(Department::class, 'departments_document_types');
    }

    /**
     * The division that belong to the DocumentType
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function division()
    {
        return $this->belongsToMany(Division::class, 'divisions_document_types', 'document_type_id', 'division_id');
    }

    /**
     * The unit that belong to the DocumentType
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function unit()
    {
        return $this->belongsToMany(Unit::class, 'unit_document_types');
    }

    public function section()
    {
        return $this->belongsToMany(Section::class, 'section_document_types', 'document_type_id', 'section_id');
    }

    public function departments()
    {
        return $this->department();
    }

    public function divisions()
    {
        return $this->division();
    }

    public function units()
    {
        return $this->unit();
    }

    public function sections()
    {
        return $this->section();
    }
}
