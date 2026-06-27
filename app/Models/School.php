<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class School extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'inep_code',
        'address',
        'director_name',
        'director_phone',
    ];

    protected static function boot()
    {
        parent::boot();

        static::created(function ($school) {
            $years = ['1º Ano', '2º Ano', '3º Ano', '4º Ano', '5º Ano'];
            $letters = ['A', 'B', 'C', 'D', 'E', 'F'];

            foreach ($years as $year) {
                foreach ($letters as $letter) {
                    SchoolClass::create([
                        'school_id' => $school->id,
                        'name' => "{$year} {$letter}",
                    ]);
                }
            }
        });
    }

    /**
     * Get the users associated with the school.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the classes associated with the school.
     */
    public function classes(): HasMany
    {
        return $this->hasMany(SchoolClass::class, 'school_id');
    }

    /**
     * Get the periods associated with the school.
     */
    public function periods(): HasMany
    {
        return $this->hasMany(Period::class);
    }
}
