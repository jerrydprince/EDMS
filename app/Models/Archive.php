<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;
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
     * Get the file that owns the Archive
     */
    public function file()
    {
        return $this->hasOne(File::class, 'archive_id', 'id');
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
    static::addGlobalScope(new \App\Models\Scopes\DepartmentScope);

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

    static::created(function($archive) {
        if (auth()->check()) {
            \App\Services\AuditTrailService::logRecord(
                'document_archived',
                'Document archived into repository: ' . ($archive->title ?? 'Archive #' . $archive->id),
                [
                    'archive_id' => $archive->id,
                    'event' => 'Document archived',
                    'document_type' => $archive->loadMissing(['archiveDocs', 'department', 'division', 'unit', 'folder']),
                    'files' => $archive->loadMissing('filePath'),
                ],
                $archive,
                \App\Services\AuditTrailService::SEVERITY_ROUTINE
            );
        }
    });

    static::updated(function($archive) {
        if (auth()->check()) {
            \App\Services\AuditTrailService::logRecord(
                'document_updated',
                'Archive document modified: ' . ($archive->title ?? 'Archive #' . $archive->id),
                [
                    'archive_id' => $archive->id,
                    'event' => 'Document updated',
                    'document_type' => $archive->loadMissing(['archiveDocs', 'department', 'division', 'unit', 'folder']),
                    'files' => $archive->loadMissing('filePath'),
                ],
                $archive,
                \App\Services\AuditTrailService::SEVERITY_NOTICE
            );
        }
    });
 
    static::deleted(function($archive) {
        if (auth()->check()) {
            \App\Services\AuditTrailService::logRecord(
                'document_deleted',
                'Archive document purged or soft-deleted: ' . ($archive->title ?? 'Archive #' . $archive->id),
                [
                    'archive_id' => $archive->id,
                    'event' => 'Document deleted',
                    'document_type' => $archive->loadMissing(['archiveDocs', 'department', 'division', 'unit', 'folder']),
                    'files' => $archive->loadMissing('filePath'),
                ],
                $archive,
                \App\Services\AuditTrailService::SEVERITY_WARNING
            );

            $archive->deleted_by = auth()->id();
            $archive->saveQuietly();
        }
    });
        
   }

//    protected $dispatchesEvents = [
//     'saved' => ArchiveSavedEvent::class,
//    ]
    
}
