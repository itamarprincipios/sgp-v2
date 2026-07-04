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
        'uploaded_by',
        'school_id',
        'period_id',
        'class_id',
        'title',
        'discipline',
        'reference_label',
        'analysis',
        'analyzed_at',
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
        'analyzed_at' => 'datetime',
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

    /**
     * Get the coordinator who uploaded the document (correction flow).
     */
    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get the school this document belongs to (correction flow).
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the class this document refers to (correction flow).
     */
    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }
}
