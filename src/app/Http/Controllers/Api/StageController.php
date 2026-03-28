<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Interfaces\StageServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StageController extends Controller
{
    public function __construct(protected StageServiceInterface $stageService)
    {
    }

    public function index(): JsonResponse
    {
        return response()->json(['data' => $this->stageService->getAllStages()]);
    }

    public function show(int $stageId): JsonResponse
    {
        $stage = $this->stageService->getStage($stageId);

        if ($stage === null) {
            return response()->json(['message' => 'Stage not found'], 404);
        }

        return response()->json(['data' => $stage]);
    }

    public function tasks(Request $request, int $stageId): JsonResponse
    {
        $userId = $request->query('userId');

        if ($userId !== null) {
            $tasks = $this->stageService->getStageTasksForUser($stageId, (int) $userId);

            return response()->json(['data' => $tasks]);
        }

        $tasks = $this->stageService->getStageTasks($stageId);

        return response()->json(['data' => $tasks]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:100|unique:quest_stages,code',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'nullable|integer',
            'stage_type' => 'nullable|string|max:100',
            'deadline_at' => 'nullable|date',
        ]);

        $stage = $this->stageService->createStage($validated);

        return response()->json(['data' => $stage], 201);
    }

    public function update(Request $request, int $stageId): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'nullable|string|max:100|unique:quest_stages,code,' . $stageId,
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'order' => 'nullable|integer',
            'stage_type' => 'nullable|string|max:100',
            'deadline_at' => 'nullable|date',
        ]);

        $stage = $this->stageService->updateStage($stageId, $validated);

        if ($stage === null) {
            return response()->json(['message' => 'Stage not found'], 404);
        }

        return response()->json(['data' => $stage]);
    }

    public function destroy(int $stageId): JsonResponse
    {
        $deleted = $this->stageService->deleteStage($stageId);

        if (! $deleted) {
            return response()->json(['message' => 'Stage not found'], 404);
        }

        return response()->json(['message' => 'Stage deleted']);
    }
}
