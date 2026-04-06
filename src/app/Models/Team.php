<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\TeamProgress;
use App\Models\QuestTask;

class Team extends Model
{
    protected $table = 'teams';

    protected $fillable = [
        'name',
        'description',
        'created_by',
    ];

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'team_members');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function progress(): HasMany
    {
        return $this->hasMany(TeamProgress::class);
    }

    public function getMembersCountAttribute()
    {
        return $this->members()->count();
    }

    public function getProgressPercentageAttribute()
    {
        $totalTasks = QuestTask::count();
        if ($totalTasks === 0) return 0;

        $completedTasks = $this->members()
            ->whereHas('submissions', function ($query) {
                $query->where('status', 'completed');
            })
            ->count();

        return round(($completedTasks / $totalTasks) * 100);
    }
}
