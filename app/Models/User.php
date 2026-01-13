<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Fortify\TwoFactorAuthenticatable;
// use LdapRecord\Models\Model;
// use LdapRecord\Laravel\Auth\LdapAuthenticatable;
// use LdapRecord\Laravel\Auth\AuthenticatesWithLdap;

/**
 * the class below is for Ldap authentication
 */
//class User extends Authenticatable implements LdapAuthenticatable
class User extends Authenticatable 
{
    /**
     * Ldap authentication below in the comment
     */
    //use HasApiTokens, HasFactory, Notifiable, AuthenticatesWithLdap;
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];
    

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Get the department that owns the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the unit that owns the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Get the division that owns the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    /**
     * The task that belong to the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function task()
    {
        return $this->belongsToMany(Task::class, 'user_task');
    }

    public function groups()
    {
        return $this->belongsToMany(groups::class, 'group_users','user_id', 'group_id');
    }

    /**
     * Get all of the rating for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function rating()
    {
        return $this->hasMany(Rating::class);
    }

    /**
     * Get all of the attendance for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function attendance()
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Get all of the certificates for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }

    /**
     * The superusertask that belong to the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function superusertask()
    {
        return $this->belongsToMany(SuperuserTask::class, 'superuser_tasks');
    }

    public function receivesBroadcastNotificationsOn(): string
    {
        return 'task.'.$this->id;
    }

    /**
     * Get all of the mails for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function mails()
    {
        return $this->hasMany(Mail::class);
    }

    /**
     * Get all of the leave for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function leave()
    {
        return $this->hasMany(Leave::class);
    }

    /**
     * Get the userSession associated with the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function userSession()
    {
        return $this->hasOne(UserSession::class);
    }
    
    protected static function booted(){
        static::deleted(function($user){
            $user->deleted_by = auth()->user()->id;
            $user->save();
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

   /**
    * Get the assignedBy associated with the User
    *
    * @return \Illuminate\Database\Eloquent\Relations\HasOne
    */
   public function assignedBy()
   {
       return $this->hasOne(User::class, 'assigned_by', 'id');
   }

   /**
    * Get all of the drafts for the User
    *
    * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
   public function drafts()
   {
       return $this->hasMany(DraftModel::class, 'user_id', 'id');
   }
}
