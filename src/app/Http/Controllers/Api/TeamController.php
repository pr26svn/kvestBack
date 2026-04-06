<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\User;
use App\Models\TeamProgress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeamController extends Controller
{
    // Убрал middleware из конструктора - он должен быть в маршрутах

    public function index(): JsonResponse
    {
        $teams = Team::withCount('members')->get();
        return response()->json(['data' => $teams]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $team = Team::create([
            ...$validated,
            'created_by' => Auth::id(),
        ]);

        // Добавляем создателя в команду
        $team->members()->attach(Auth::id());

        return response()->json(['data' => $team], 201);
    }

    public function show(Team $team): JsonResponse
    {
        $team->loadCount('members');
        return response()->json(['data' => $team]);
    }

    public function update(Request $request, Team $team): JsonResponse
    {
        $this->authorize('update', $team);

        $validated = $request->validate([
            'name' => 'string|max:255',
            'description' => 'nullable|string',
        ]);

        $team->update($validated);
        return response()->json(['data' => $team]);
    }

    public function destroy(Team $team): JsonResponse
    {
        $this->authorize('delete', $team);
        $team->delete();
        return response()->json(['message' => 'Team deleted successfully']);
    }

    public function join(Request $request, Team $team): JsonResponse
    {
        $user = Auth::user();

        if (!$team->members()->where('user_id', $user->id)->exists()) {
            $team->members()->attach($user->id);
        }

        return response()->json(['message' => 'Joined team successfully']);
    }

    public function members(Team $team): JsonResponse
    {
        $members = $team->members()->get();
        return response()->json(['data' => $members]);
    }

    public function progress(Team $team): JsonResponse
    {
        $progress = TeamProgress::where('team_id', $team->id)
            ->with(['stage', 'user'])
            ->get()
            ->groupBy('stage_id');

        return response()->json(['data' => $progress]);
    }

    public function rankings(): JsonResponse
    {
        $rankings = Team::withCount('members')
            ->get()
            ->map(function ($team) {
                $completedCount = $team->members()
                    ->withCount(['submissions' => function ($query) {
                        $query->where('status', 'completed');
                    }])
                    ->get()
                    ->sum('submissions_count');

                $totalMembers = $team->members_count ?? 1;
                $progress = round(($completedCount / ($totalMembers * 10)) * 100, 0); // 10 = примерное кол-во заданий

                return [
                    'id' => $team->id,
                    'name' => $team->name,
                    'progress' => min($progress, 100),
                    'members_count' => $totalMembers,
                ];
            })
            ->sortByDesc('progress')
            ->values()
            ->all();

        return response()->json(['data' => $rankings]);
    }

    public function userTeams(): JsonResponse
    {
        $user = Auth::user();
        $teams = $user->teams()->withCount('members')->get();
        return response()->json(['data' => $teams]);
    }
}
