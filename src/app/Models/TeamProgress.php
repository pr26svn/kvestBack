<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamProgress extends Model
{
    protected $table = 'team_progress';

    protected $fillable = [
        'team_id',
        'stage_id',
        'user_id',
        'stage_progress',
        'user_progress',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(QuestStage::class, 'stage_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
