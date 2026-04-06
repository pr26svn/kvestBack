<?php

namespace App\Policies;

use App\Models\Team;
use App\Models\User;

class TeamPolicy
{
    public function update(User $user, Team $team): bool
    {
        return $user->id === $team->created_by;
    }

    public function delete(User $user, Team $team): bool
    {
        return $user->id === $team->created_by;
    }

    public function join(User $user, Team $team): bool
    {
        return true; // Любой может присоединиться
    }
}
