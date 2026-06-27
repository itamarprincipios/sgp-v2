<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Period extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'school_id',
        'name',
        'description',
        'bimester',
        'start_date',
        'end_date',
        'deadline',
        'opening_date',
        'is_active',
        'is_physical_education',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'date',
        'deadline' => 'datetime',
        'opening_date' => 'datetime',
        'is_active' => 'boolean',
        'is_physical_education' => 'boolean',
    ];

    /**
     * Get the school that owns the period (null for global periods).
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the documents associated with this period.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }
}
