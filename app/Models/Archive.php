<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Jenssegers\Agent\Agent;

class Archive extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Get the department that owns the Archive
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the division that owns the Archive
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    /**
     * Get the unit that owns the Archive
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

 /**
  * Get all of the archiveDocs for the Archive
  *
  * @return \Illuminate\Database\Eloquent\Relations\HasMany
  */
 public function archiveDocs()
 {
     return $this->hasMany(ArchiveDocs::class);
 }


   /**
    * The filePath that belong to the Archive
    *
    * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
    */
   public function filePath()
   {
       return $this->belongsToMany(FilePath::class, 'archive_file_path');
   }

   /**
    * Get all of the fileActivity for the Archive
    *
    * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
   public function fileActivity()
   {
       return $this->hasMany(FileActivity::class);
   }

   /**
    * Get all of the versions for the Archive
    *
    * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
   public function version()
   {
       return $this->hasMany(Version::class);
   }

   /**
    * Get all of the folder for the Archive
    *
    * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
   public function folder()
   {
       return $this->hasOne(Folder::class, 'id', 'folder_id');
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

   protected static function booted(){

    $agent = new Agent();

    $isMobile = $agent->isMobile();   // Returns true if the device is a mobile
    $isTablet = $agent->isTablet();   // Returns true if the device is a tablet
    $isDesktop = $agent->isDesktop(); // Returns true if the device is a desktop
    $browser = $agent->browser();     // e.g., Chrome, Safari
    $platform = $agent->platform();   // e.g., iOS, Android, Windows

    $device = '';

    // Example usage
    if ($isMobile) {
        $device = 'Mobile';
    } elseif ($isTablet) {
        $device = 'Tablet';
    } else {
        $device = 'Desktop';
    }

    static::created(function($archive) use($device){
    
        activity()
        ->performedOn($archive)
        ->causedBy(auth()->user()->id)
        ->withProperties([
            'archive' => 'archive created',
            'user' => auth()->user()->first_name.' '.auth()->user()->last_name,
            'ip' => request()->ip(),
            'event' => 'Document archived',
            'user_agent' => request()->userAgent(),
            'device' => $device,
            'document_type' =>$archive->load(['archiveDocs','department', 'division','unit', 'folder']),
            'files' => $archive->load('filePath'),
            ])
        ->log('Archive was created by '.auth()->user()->first_name.' '.auth()->user()->last_name);
            });

            static::updated(function($archive) use($device){
                activity()
                ->performedOn($archive)
                ->causedBy(auth()->user()->id)
                ->withProperties([
                    'archive' => 'archive updated',
                    'ip' => request()->ip(),
                    'user' => auth()->user()->first_name.' '.auth()->user()->last_name,
                    'user_agent' => request()->userAgent(),
                    'event' => 'Document updated',
                    'device' => $device,
                    'document_type' =>$archive->load(['archiveDocs','department', 'division','unit', 'folder']),
                    'files' => $archive->load('filePath'),
                    ])
                ->log('Archive was updated by '.auth()->user()->first_name.' '.auth()->user()->last_name);
               });
            
               static::deleted(function($archive) use($device){
                activity()
                    ->performedOn($archive)
                    ->causedBy(auth()->user()->id)
                    ->withProperties([
                        'archive' => 'archive deleted',
                        'ip' => request()->ip(),
                        'event' => 'Document deleted',
                        'user' => auth()->user()->first_name.' '.auth()->user()->last_name,
                        'user_agent' => request()->userAgent(),
                        'device' => $device,
                        'document_type' =>$archive->load(['archiveDocs','department', 'division','unit', 'folder']),
                        'files' => $archive->load('filePath'),
                        ])
                    ->log('Archive was deleted by '.auth()->user()->first_name.' '.auth()->user()->last_name);

                    $archive->deleted_by = auth()->user()->id;
                    $archive->save();
               });

               static::retrieved(function($archive) use($device){
    
                activity()
                ->performedOn($archive)
                ->causedBy(auth()->user()->id)
                ->withProperties([
                    'archive' => 'archive viewed',
                    'ip' => request()->ip(),
                    'event' => 'Document viewed',
                    'user' => auth()->user()->first_name.' '.auth()->user()->last_name,
                    'user_agent' => request()->userAgent(),
                    'device' => $device,
                    'document_type' =>$archive->load(['archiveDocs','department', 'division','unit', 'folder']),
                    'files' => $archive->load('filePath'),
                    ])
                ->log('Archive was viewed by '.auth()->user()->first_name.' '.auth()->user()->last_name);
                    });
        
   }

//    protected $dispatchesEvents = [
//     'saved' => ArchiveSavedEvent::class,
//    ]
    
}
