<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'period_id',
        'title',
        'type',
        'file_path',
        'content_text',
        'content_extracted_at',
        'status',
        'feedback',
        'score_base',
        'penalty_delay',
        'penalty_resubmission',
        'score_final',
        'rejection_count',
        'rejected_at',
        'submitted_at',
    ];

    protected $casts = [
        'content_extracted_at' => 'datetime',
        'score_base' => 'decimal:2',
        'penalty_delay' => 'decimal:2',
        'penalty_resubmission' => 'decimal:2',
        'score_final' => 'decimal:2',
        'rejected_at' => 'datetime',
        'submitted_at' => 'datetime',
    ];

    /**
     * Get the user who owns the document.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the period this document belongs to.
     */
    public function period(): BelongsTo
    {
        return $this->belongsTo(Period::class);
    }
}
