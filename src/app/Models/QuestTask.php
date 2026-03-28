<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestTask extends Model
{
    protected $table = 'quest_tasks';

    protected $fillable = [
        'quest_stage_id',
        'title',
        'description',
        'task_type',
        'difficulty',
        'order',
        'max_score',
        'payload',
        'required',
    ];

    protected $casts = [
        'payload' => 'array',
        'required' => 'boolean',
    ];

    public function stage(): BelongsTo
    {
        return $this->belongsTo(QuestStage::class, 'quest_stage_id');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(TaskSubmission::class, 'quest_task_id');
    }
}
