<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Assessment extends Model
{
    protected $table = 'assessments';

    protected $fillable = [
        'task_submission_id',
        'evaluator_id',
        'score',
        'comment',
        'rubric',
    ];

    protected $casts = [
        'rubric' => 'array',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(TaskSubmission::class, 'task_submission_id');
    }
}
