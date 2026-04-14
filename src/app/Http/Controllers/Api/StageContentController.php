<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\QuestStage;
use App\Models\StageContent;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class StageContentController extends Controller
{
    public function show($stageId): JsonResponse
    {
        $stage = QuestStage::findOrFail($stageId);
        $content = $stage->content;

        if (!$content || !$content->is_active) {
            return response()->json([
                'data' => null,
                'message' => 'Содержимое этапа не найдено'
            ], 404);
        }

        return response()->json([
            'data' => $content,
        ]);
    }

    public function store(Request $request, $stageId): JsonResponse
    {
        $stage = QuestStage::findOrFail($stageId);

        $validated = $request->validate([
            'html_content' => 'required|string',
            'content_type' => 'in:html,iframe,component',
            'is_active' => 'boolean',
            'settings' => 'nullable|json',
        ]);

        $content = StageContent::updateOrCreate(
            ['quest_stage_id' => $stageId],
            [
                ...$validated,
                'updated_by' => auth()->user()?->id,
                'created_by' => auth()->user()?->id,
            ]
        );

        return response()->json([
            'data' => $content,
            'message' => 'Содержимое этапа обновлено'
        ], 201);
    }

    public function update(Request $request, $stageId): JsonResponse
    {
        $content = StageContent::where('quest_stage_id', $stageId)->firstOrFail();

        $validated = $request->validate([
            'html_content' => 'required|string',
            'content_type' => 'in:html,iframe,component',
            'is_active' => 'boolean',
            'settings' => 'nullable|json',
        ]);

        $content->update([
            ...$validated,
            'updated_by' => auth()->id(),
        ]);

        return response()->json([
            'data' => $content,
            'message' => 'Содержимое этапа обновлено'
        ]);
    }

    public function destroy($stageId): JsonResponse
    {
        $content = StageContent::where('quest_stage_id', $stageId)->firstOrFail();
        $content->delete();

        return response()->json([
            'message' => 'Содержимое этапа удалено'
        ]);
    }
}
