<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserMedal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'medal_type',
        'period_type',
        'reference_date',
    ];

    protected $casts = [
        'reference_date' => 'date',
    ];

    /**
     * Get the user who owns this medal record.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
