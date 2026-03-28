<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Interfaces\TaskServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function __construct(protected TaskServiceInterface $taskService)
    {
    }

    public function index(): JsonResponse
    {
        return response()->json(['data' => $this->taskService->getAllTasks()]);
    }

    public function show(int $taskId): JsonResponse
    {
        $task = $this->taskService->getTask($taskId);

        if ($task === null) {
            return response()->json(['message' => 'Task not found'], 404);
        }

        return response()->json(['data' => $task]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'quest_stage_id' => 'required|integer|exists:quest_stages,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'task_type' => 'nullable|string|max:100',
            'difficulty' => 'nullable|string|max:50',
            'order' => 'nullable|integer',
            'max_score' => 'nullable|integer',
            'payload' => 'nullable|array',
            'required' => 'nullable|boolean',
        ]);

        $task = $this->taskService->createTask($validated);

        return response()->json(['data' => $task], 201);
    }

    public function update(Request $request, int $taskId): JsonResponse
    {
        $validated = $request->validate([
            'quest_stage_id' => 'nullable|integer|exists:quest_stages,id',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'task_type' => 'nullable|string|max:100',
            'difficulty' => 'nullable|string|max:50',
            'order' => 'nullable|integer',
            'max_score' => 'nullable|integer',
            'payload' => 'nullable|array',
            'required' => 'nullable|boolean',
        ]);

        $task = $this->taskService->updateTask($taskId, $validated);

        if ($task === null) {
            return response()->json(['message' => 'Task not found'], 404);
        }

        return response()->json(['data' => $task]);
    }

    public function destroy(int $taskId): JsonResponse
    {
        $deleted = $this->taskService->deleteTask($taskId);

        if (! $deleted) {
            return response()->json(['message' => 'Task not found'], 404);
        }

        return response()->json(['message' => 'Task deleted']);
    }
}
