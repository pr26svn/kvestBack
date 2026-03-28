<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaskSubmission extends Model
{
    protected $table = 'task_submissions';

    protected $fillable = [
        'quest_task_id',
        'user_id',
        'answer_text',
        'answer_data',
        'status',
        'score',
        'feedback',
        'submitted_at',
    ];

    protected $casts = [
        'answer_data' => 'array',
        'submitted_at' => 'datetime',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(QuestTask::class, 'quest_task_id');
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class, 'task_submission_id');
    }
}
