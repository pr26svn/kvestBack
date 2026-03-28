<?php

namespace App\Services;

use App\Models\Assessment;
use App\Models\TaskSubmission;
use App\Services\Interfaces\AssessmentServiceInterface;

class AssessmentService implements AssessmentServiceInterface
{
    public function createAssessment(int $submissionId, array $data): object
    {
        $submission = TaskSubmission::findOrFail($submissionId);

        $assessment = Assessment::create([
            'task_submission_id' => $submissionId,
            'evaluator_id' => $data['evaluator_id'],
            'score' => $data['score'] ?? null,
            'comment' => $data['comment'] ?? null,
            'rubric' => $data['rubric'] ?? null,
        ]);

        if (isset($data['score'])) {
            $submission->update([
                'score' => $data['score'],
                'status' => 'assessed',
            ]);
        } else {
            $submission->update([
                'status' => 'reviewed',
            ]);
        }

        return $assessment;
    }
}
