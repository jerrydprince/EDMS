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
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, TwoFactorAuthenticatable;
    use HasRoles {
        hasRole as traitHasRole;
        hasPermissionTo as traitHasPermissionTo;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'email',
        'phone',
        'gender',
        'password',
        'approval_pin',
        'digital_signature_path',
        'designation_id',
        'grade_level',
        'ippis_no',
        'step',
        'date_of_birth',
        'date_of_first_appointment',
        'date_of_confirmation',
        'department_id',
        'division_id',
        'unit_id',
        'section_id',
        'is_department_head',
        'is_division_head',
        'is_unit_head',
        'is_section_head',
        'is_approved',
        'is_on_leave',
        'theme',
        'font_size',
        'density',
        'default_landing_page',
        'language',
        'time_format',
        'date_format',
        'timezone',
        'currency',
        'notification_settings',
    ];
    

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'approval_pin',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'notification_settings' => 'array',
    ];

    protected $appends = [
        'name',
    ];

    /**
     * Get the user's full name.
     *
     * @return string
     */
    public function getNameAttribute()
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    /**
     * Determine if user is currently online/logged in.
     *
     * @return int
     */
    public function getIsOnlineAttribute($value)
    {
        if (\Illuminate\Support\Facades\Cache::has('user-is-online-' . $this->id)) {
            return 1;
        }

        if (auth()->check() && (int)auth()->id() === (int)$this->id) {
            return 1;
        }

        return 0;
    }

    /**
     * Get the designation that owns the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function designation()
    {
        return $this->belongsTo(Designation::class);
    }

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

    public function section()
    {
        return $this->belongsTo(Section::class);
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

    public function taskAssignments()
    {
        return $this->hasMany(\App\Models\TaskAssignment::class, 'assigned_to');
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

    public function leaves()
    {
        return $this->hasMany(Leave::class);
    }

    public function activeLeave()
    {
        $today = now()->toDateString();
        return $this->hasOne(Leave::class)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->latest('id');
    }

    public function misconducts()
    {
        return $this->hasMany(Misconduct::class, 'user_id');
    }

    public function reportedMisconducts()
    {
        return $this->hasMany(Misconduct::class, 'reported_by');
    }

    public function issuedQueries()
    {
        return $this->hasMany(StaffQuery::class, 'issued_by');
    }

    public function receivedQueries()
    {
        return $this->hasMany(StaffQuery::class, 'issued_to');
    }

    public function servicomReportsMade()
    {
        return $this->hasMany(ServicomReport::class, 'complainant_id');
    }

    public function servicomReportsAgainst()
    {
        return $this->hasMany(ServicomReport::class, 'accused_user_id');
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
            $user->deleted_by = auth()->id();
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

    /**
     * Override hasRole to normalize singular/plural aliases (e.g. 'super user' <-> 'super users',
     * 'director' <-> 'directors') and gracefully handle missing role definitions.
     */
    public function hasRole($roles, ?string $guard = null): bool
    {
        $this->loadMissing('roles');
        $userRoles = $this->roles
            ->when($guard, fn ($q) => $q->where('guard_name', $guard))
            ->pluck('name')
            ->map(fn($n) => strtolower(trim($n)))
            ->toArray();

        $roleMap = [
            'super user' => ['super user', 'super users'],
            'super users' => ['super user', 'super users'],
            'director' => ['director', 'directors'],
            'directors' => ['director', 'directors'],
            'deputy director' => ['deputy director', 'division heads', 'division head'],
            'division head' => ['deputy director', 'division heads', 'division head'],
            'division heads' => ['deputy director', 'division heads', 'division head'],
            'assistant director' => ['assistant director', 'unit heads', 'unit head'],
            'unit head' => ['assistant director', 'unit heads', 'unit head'],
            'unit heads' => ['assistant director', 'unit heads', 'unit head'],
            'regular user' => ['regular user', 'regular users'],
            'regular users' => ['regular user', 'regular users'],
            'record manager' => ['record manager', 'record managers'],
        ];

        // Flatten nested arrays recursively (e.g. from hasAnyRole variadics)
        $flattened = \Illuminate\Support\Arr::flatten(is_array($roles) ? $roles : (is_string($roles) ? explode('|', $roles) : [$roles]));

        $checkList = [];
        foreach ($flattened as $r) {
            if (is_string($r)) {
                $parts = strpos($r, '|') !== false ? explode('|', $r) : [$r];
                foreach ($parts as $p) {
                    $name = strtolower(trim($p));
                    if (isset($roleMap[$name])) {
                        $checkList = array_merge($checkList, $roleMap[$name]);
                    } else {
                        $checkList[] = $name;
                    }
                }
            } elseif ($r instanceof \BackedEnum) {
                $checkList[] = strtolower(trim($r->value));
            } elseif (is_object($r) && isset($r->name)) {
                $name = strtolower(trim($r->name));
                if (isset($roleMap[$name])) {
                    $checkList = array_merge($checkList, $roleMap[$name]);
                } else {
                    $checkList[] = $name;
                }
            } elseif (is_scalar($r)) {
                $checkList[] = strtolower(trim((string)$r));
            }
        }
        $checkList = array_unique($checkList);

        // Also check AGF Office implicit roles if applicable
        if ($this->department && strtolower(trim($this->department->title)) === 'agf office') {
            if (in_array('accountant-general', $userRoles) || in_array('super user', $userRoles) || in_array('super users', $userRoles)) {
                if (in_array('super user', $checkList) || in_array('super users', $checkList) || in_array('agf', $checkList)) {
                    return true;
                }
            }
        }

        // Direct matching against user's loaded roles
        foreach ($checkList as $candidate) {
            if (in_array(strtolower(trim($candidate)), $userRoles)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Override hasPermissionTo to also check department permissions
     */
    public function hasPermissionTo($permission, $guardName = null): bool
    {
        try {
            if ($this->traitHasPermissionTo($permission, $guardName)) {
                return true;
            }
        } catch (\Spatie\Permission\Exceptions\PermissionDoesNotExist $e) {
            // gracefully fallback to department or false
        }

        try {
            if ($this->department && $this->department->hasPermissionTo($permission, $guardName)) {
                return true;
            }
        } catch (\Throwable $e) {
            return false;
        }

        return false;
    }

    /**
     * Get the active hierarchy level of the user based on placement
     */
    public function getHierarchyLevel(): string
    {
        if ($this->unit_id) return 'unit';
        if ($this->division_id) return 'division';
        if ($this->department_id) return 'department';
        return 'global';
    }

    /**
     * Check if user is a designated Head of Office / Director / Head of Division / Unit
     */
    public function isHeadOfOffice(): bool
    {
        if ($this->is_department_head || $this->is_division_head || $this->is_unit_head || $this->is_section_head) {
            return true;
        }

        if ($this->designation && $this->designation->is_head_of_office) {
            return true;
        }

        return $this->hasRole(['Accountant-General', 'director', 'deputy director', 'assistant director']);
    }

    /**
     * Check if user is a Registry Officer (regardless of hierarchy tier)
     */
    public function isRegistryOfficer(): bool
    {
        if ($this->designation && $this->designation->is_registry) {
            return true;
        }

        if ($this->hasAnyRole(['AGF Registry', 'record manager', 'AGF record manager', 'Registry Officer'])) {
            return true;
        }

        return $this->hasPermissionTo('registry.view');
    }

    /**
     * Check if user is Central / Ministerial Registry (AGF Office or global tier)
     */
    public function isCentralRegistry(): bool
    {
        if (!$this->isRegistryOfficer()) {
            return false;
        }

        if ($this->designation && $this->designation->hierarchy_level === 'global') {
            return true;
        }

        if ($this->department && stripos($this->department->title, 'agf') !== false) {
            return true;
        }

        return $this->hasRole('AGF Registry') || $this->hasRole('AGF record manager');
    }

    /**
     * Check if user is Departmental Registry for a specific or any department
     */
    public function isDepartmentalRegistry(?int $departmentId = null): bool
    {
        if (!$this->isRegistryOfficer() || $this->isCentralRegistry()) {
            return false;
        }

        if ($departmentId !== null && $this->department_id != $departmentId) {
            return false;
        }

        return !empty($this->department_id) && empty($this->division_id) && empty($this->unit_id);
    }

    /**
     * Check if user is Division Registry
     */
    public function isDivisionRegistry(?int $divisionId = null): bool
    {
        if (!$this->isRegistryOfficer()) {
            return false;
        }

        if ($divisionId !== null && $this->division_id != $divisionId) {
            return false;
        }

        return !empty($this->division_id) && empty($this->unit_id);
    }

    /**
     * Check if user is Unit Registry
     */
    public function isUnitRegistry(?int $unitId = null): bool
    {
        if (!$this->isRegistryOfficer()) {
            return false;
        }

        if ($unitId !== null && $this->unit_id != $unitId) {
            return false;
        }

        return !empty($this->unit_id);
    }

    /**
     * Query registry users appropriate for a given hierarchy placement
     */
    public function scopeRegistryInHierarchy($query, $deptId = null, $divId = null, $unitId = null)
    {
        return $query->where(function ($q) use ($deptId, $divId, $unitId) {
            // 1. Matched by designation flag
            $q->whereHas('designation', function ($dq) use ($deptId, $divId, $unitId) {
                $dq->where('is_registry', true);
            });

            // 2. Or matched by classic roles
            $q->orWhereHas('roles', function ($rq) {
                $rq->whereIn('name', ['AGF Registry', 'record manager', 'AGF record manager', 'Registry Officer']);
            });
        })->where(function ($loc) use ($deptId, $divId, $unitId) {
            if ($unitId) {
                $loc->where('unit_id', $unitId);
            } elseif ($divId) {
                $loc->where('division_id', $divId);
            } elseif ($deptId) {
                $loc->where('department_id', $deptId);
            } else {
                $loc->whereNull('department_id')
                    ->orWhereHas('department', function ($dq) {
                        $dq->where('title', 'like', '%AGF%');
                    });
            }
        });
    }

    /**
     * Scope users visible or actionable based on the viewer's organizational hierarchy
     */
    public function scopeInHierarchyScope($query, User $viewer)
    {
        if ($viewer->hasAnyRole(['super admin', 'super user', 'Accountant-General']) || $viewer->isCentralRegistry()) {
            return $query; // Global visibility
        }

        if ($viewer->hasRole('director') || $viewer->isDepartmentalRegistry()) {
            return $query->where('department_id', $viewer->department_id);
        }

        if ($viewer->hasRole('deputy director') || $viewer->isDivisionRegistry()) {
            return $query->where('division_id', $viewer->division_id);
        }

        if ($viewer->hasRole('assistant director') || $viewer->isUnitRegistry()) {
            return $query->where('unit_id', $viewer->unit_id);
        }

        return $query->where('id', $viewer->id);
    }
}

