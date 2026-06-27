<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiQuery extends Model
{
    use HasFactory;

    protected $table = 'ai_queries';

    protected $fillable = [
        'user_id',
        'question',
        'context_filters',
        'response',
        'response_time_ms',
    ];

    protected $casts = [
        'context_filters' => 'array',
    ];

    /**
     * Get the user who made the query.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
