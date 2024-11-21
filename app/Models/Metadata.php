<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Metadata extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'string', 'is_default'];

    protected function isDefault(): Attribute
    {
        return Attribute::make(
            get: fn (boolean $value) => $value==1? "Default": "Variable",
        );
    }

    //returns a belongs to many relationship
    public function documentType(){
        return $this->belongsToMany(DocumentType::class);
    }

    protected static function booted(){
        static::deleted(function($meta){
            $meta->deleted_by = auth()->user()->id;
            $meta->save();
        });
    }

    /**
    * Get the deletedBy associated with the Archive
    *
    * @return \Illuminate\Database\Eloquent\Relations\HasOne
    */
   public function deletedBy()
   {
       return $this->hasOne(User::class, 'id', 'deleted_by');
   }
}
