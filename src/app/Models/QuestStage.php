<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestStage extends Model
{
    protected $table = 'quest_stages';

    protected $fillable = [
        'code',
        'title',
        'description',
        'order',
        'stage_type',
        'deadline_at',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
        'deadline_at' => 'date',
    ];

    public function tasks(): HasMany
    {
        return $this->hasMany(QuestTask::class, 'quest_stage_id');
    }
}
