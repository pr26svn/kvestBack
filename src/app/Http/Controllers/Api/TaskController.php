<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Interfaces\TaskServiceInterface;
use Illuminate\Http\JsonResponse;

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
}
