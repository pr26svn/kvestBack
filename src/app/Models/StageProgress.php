<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StageProgress extends Model
{
    protected $table = 'stage_progress';

    protected $fillable = [
        'user_id',
        'quest_stage_id',
        'started_at',
        'completed_at',
        'score',
        'status',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];
}
