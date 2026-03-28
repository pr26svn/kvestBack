<?php

namespace App\Services\Interfaces;

interface AssessmentServiceInterface
{
    public function createAssessment(int $submissionId, array $data): object;
}
