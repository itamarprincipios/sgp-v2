<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'is_active',
        'ai_enabled',
        'max_schools_limit',
        'expires_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'ai_enabled' => 'boolean',
        'expires_at' => 'datetime',
    ];

    /**
     * Get the schools belonging to this tenant.
     */
    public function schools(): HasMany
    {
        return $this->hasMany(School::class);
    }

    /**
     * Get the users belonging to this tenant.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the planning periods belonging to this tenant.
     */
    public function periods(): HasMany
    {
        return $this->hasMany(Period::class);
    }
}
