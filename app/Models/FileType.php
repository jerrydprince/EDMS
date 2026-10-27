<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FileType extends Model
{
    use HasFactory;

    protected $casts = [
        'headings' => 'array',
    ];

    /**
     * The department that belong to the FileType
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function department()
    {
        return $this->belongsToMany(Department::class, 'department_file_type');
    }

    /**
     * The division that belong to the FileType
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function division()
    {
        return $this->belongsToMany(Division::class, 'division_file_type');
    }

    /**
     * The unit that belong to the FileType
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function unit()
    {
        return $this->belongsToMany(Unit::class, 'unit_file_type');
    }

    /**
     * The section that belong to the FileType
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function section()
    {
        return $this->belongsToMany(Section::class, 'section_file_type', 'file_type_id', 'section_id');
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

    /**
     * Get the folder that owns the FileType
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function folder()
    {
        return $this->belongsTo(Folder::class);
    }
}
