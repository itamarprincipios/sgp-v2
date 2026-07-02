<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, BelongsToTenant;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'tenant_id',
        'school_id',
        'class_id',
        'monitor_class_id',
        'role',
        'whatsapp',
        'is_physical_education',
        'is_monitor',
        'is_first_grade',
        'profile_photo',
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
        'is_physical_education' => 'boolean',
        'is_monitor' => 'boolean',
        'is_first_grade' => 'boolean',
    ];

    /**
     * Get the dashboard path for this user based on their role.
     */
    public function dashboardPath(): string
    {
        return match ($this->role) {
            'superadmin' => '/superadmin/dashboard',
            'admin' => '/admin/dashboard',
            'semed' => '/semed/dashboard',
            'director', 'vice_director', 'coordinator' => '/school/dashboard',
            'professor' => '/professor/dashboard',
            'supervisor_edfis' => '/supervisor-edfis/dashboard',
            'supervisor_monitor' => '/supervisor-monitor/dashboard',
            'supervisor_infantil' => '/supervisor-infantil/dashboard',
            'supervisor_fundamental' => '/supervisor-fundamental/dashboard',
            default => '/dashboard',
        };
    }

    /**
     * Get the school associated with this user.
     */
    public function school(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the schools associated with this user (pivot).
     */
    public function schools(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(School::class, 'user_schools');
    }

    /**
     * Get all assigned school IDs.
     */
    public function getAssignedSchoolIds(): array
    {
        $schoolIds = $this->schools()->pluck('schools.id')->toArray();
        if ($this->school_id && !in_array($this->school_id, $schoolIds)) {
            $schoolIds[] = $this->school_id;
        }
        return $schoolIds;
    }

    /**
     * Get the class associated with this user.
     */
    public function schoolClass(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    /**
     * Get the monitor class associated with this user.
     */
    public function monitorClass(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'monitor_class_id');
    }

    /**
     * Get the documents associated with this user.
     */
    public function documents(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Document::class);
    }

    /**
     * Get the medals won by this user.
     */
    public function medals(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UserMedal::class);
    }

    /**
     * Get the AI queries made by this user.
     */
    public function aiQueries(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AiQuery::class);
    }

    /**
     * Get the official notices sent by this user.
     */
    public function sentNotices(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Notice::class, 'sender_id');
    }

    /**
     * Get the official notices received by this user.
     */
    public function receivedNotices(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Notice::class, 'recipient_id');
    }
}
