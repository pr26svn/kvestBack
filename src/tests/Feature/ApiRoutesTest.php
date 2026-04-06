<?php

namespace Tests\Feature;

use App\Models\QuestStage;
use App\Models\QuestTask;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiRoutesTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $user;
    protected User $moderator;
    protected QuestStage $stage;
    protected QuestTask $task;
    protected Team $team;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'email' => 'admin@example.com',
        ]);

        $this->user = User::factory()->create([
            'role' => 'user',
            'email' => 'user@example.com',
        ]);

        $this->moderator = User::factory()->create([
            'role' => 'user',
            'email' => 'moderator@example.com',
        ]);

        $role = Role::create([
            'name' => 'moderator',
            'label' => 'Moderator',
        ]);
        $role->users()->attach($this->moderator->id);

        $this->stage = QuestStage::create([
            'code' => 'stage-1',
            'title' => 'Stage One',
            'description' => 'First stage',
            'order' => 1,
            'stage_type' => 'online',
            'deadline_at' => now()->addDays(7),
        ]);

        $this->task = QuestTask::create([
            'quest_stage_id' => $this->stage->id,
            'title' => 'Essay Task',
            'description' => 'Answer the question',
            'task_type' => 'essay',
            'difficulty' => 'easy',
            'order' => 1,
            'max_score' => 10,
            'payload' => ['question' => 'What is 2 + 2?'],
            'required' => true,
        ]);

        $this->team = Team::create([
            'name' => 'Blue Team',
            'description' => 'A sample team',
            'created_by' => $this->admin->id,
        ]);
        $this->team->members()->attach($this->admin->id);
    }

    public function test_auth_endpoints_work_as_expected(): void
    {
        $registerResponse = $this->postJson('/api/register', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $registerResponse->assertStatus(201)
            ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email', 'role']]);

        $loginResponse = $this->postJson('/api/login', [
            'email' => 'jane@example.com',
            'password' => 'secret123',
        ]);

        $loginResponse->assertStatus(200)
            ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email', 'role']]);

        $token = $loginResponse->json('token');

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/me')
            ->assertStatus(200)
            ->assertJson(['user' => ['email' => 'jane@example.com']]);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/logout')
            ->assertStatus(200)
            ->assertJson(['message' => 'Logged out successfully']);

        $this->postJson('/api/login', [
            'email' => 'jane@example.com',
            'password' => 'wrong-password',
        ])->assertStatus(401);
    }

    public function test_public_stage_and_task_routes_are_accessible(): void
    {
        $this->getJson('/api/stages')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [['id', 'code', 'title', 'tasks']]]);

        $this->getJson("/api/stages/{$this->stage->id}")
            ->assertStatus(200)
            ->assertJson(['data' => ['id' => $this->stage->id, 'code' => 'stage-1']]);

        $this->getJson("/api/stages/{$this->stage->id}/tasks")
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [['id', 'quest_stage_id', 'title', 'task_type']]]);

        $this->getJson('/api/tasks')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [['id', 'title', 'task_type', 'stage']]]);

        $this->getJson("/api/tasks/{$this->task->id}")
            ->assertStatus(200)
            ->assertJson(['data' => ['id' => $this->task->id, 'title' => 'Essay Task']]);
    }

    public function test_team_routes_and_user_team_endpoints(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/teams', [
                'name' => 'Red Team',
                'description' => 'Created by admin',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['data' => ['id', 'name', 'description']]);

        $teamId = $response->json('data.id');

        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/teams', ['name' => 'User Team'])
            ->assertStatus(403);

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/teams/{$teamId}/join")
            ->assertStatus(200)
            ->assertJson(['message' => 'Joined team successfully']);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/teams/{$teamId}/members")
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [['id', 'name', 'email', 'role']]]);

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/users/teams')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [['id', 'name']]]);

        $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/teams/{$teamId}", ['name' => 'Red Team Updated'])
            ->assertStatus(200)
            ->assertJson(['data' => ['name' => 'Red Team Updated']]);

        $this->actingAs($this->admin, 'sanctum')
            ->getJson("/api/teams/{$teamId}")
            ->assertStatus(200)
            ->assertJson(['data' => ['id' => $teamId, 'name' => 'Red Team Updated']]);

        $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/teams/rankings')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [['id', 'name', 'progress', 'members_count']]]);

        $this->actingAs($this->admin, 'sanctum')
            ->getJson("/api/teams/{$teamId}/progress")
            ->assertStatus(200)
            ->assertJsonStructure(['data']);

        $this->actingAs($this->admin, 'sanctum')
            ->deleteJson("/api/teams/{$teamId}")
            ->assertStatus(200)
            ->assertJson(['message' => 'Team deleted successfully']);
    }

    public function test_admin_can_manage_team_members(): void
    {
        $this->actingAs($this->admin, 'sanctum')
            ->postJson("/api/admin/teams/{$this->team->id}/add-user", ['user_id' => $this->user->id])
            ->assertStatus(200)
            ->assertJson(['message' => 'User added to team successfully']);

        $this->actingAs($this->admin, 'sanctum')
            ->getJson("/api/teams/{$this->team->id}/members")
            ->assertStatus(200)
            ->assertJsonFragment(['email' => $this->user->email]);

        $this->actingAs($this->admin, 'sanctum')
            ->deleteJson("/api/admin/teams/{$this->team->id}/remove-user/{$this->user->id}")
            ->assertStatus(200)
            ->assertJson(['message' => 'User removed from team successfully']);
    }

    public function test_admin_stage_and_task_crud_routes(): void
    {
        $stageResponse = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/admin/stages', [
                'code' => 'stage-admin',
                'title' => 'Admin Stage',
                'description' => 'Created by admin',
                'order' => 2,
                'stage_type' => 'offline',
            ]);

        $stageResponse->assertStatus(201)
            ->assertJsonStructure(['data' => ['id', 'code', 'title']]);

        $newStageId = $stageResponse->json('data.id');

        $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/admin/stages/{$newStageId}", ['title' => 'Admin Stage Updated'])
            ->assertStatus(200)
            ->assertJson(['data' => ['title' => 'Admin Stage Updated']]);

        $taskResponse = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/admin/tasks', [
                'quest_stage_id' => $newStageId,
                'title' => 'Admin Task',
                'description' => 'Task for admin route',
                'task_type' => 'essay',
                'difficulty' => 'medium',
                'order' => 1,
                'max_score' => 5,
                'payload' => ['prompt' => 'Write something'],
                'required' => true,
            ]);

        $taskResponse->assertStatus(201)
            ->assertJsonStructure(['data' => ['id', 'title', 'quest_stage_id']]);

        $newTaskId = $taskResponse->json('data.id');

        $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/admin/tasks/{$newTaskId}", ['title' => 'Admin Task Updated'])
            ->assertStatus(200)
            ->assertJson(['data' => ['title' => 'Admin Task Updated']]);

        $this->actingAs($this->admin, 'sanctum')
            ->deleteJson("/api/admin/tasks/{$newTaskId}")
            ->assertStatus(200)
            ->assertJson(['message' => 'Task deleted']);

        $this->actingAs($this->admin, 'sanctum')
            ->deleteJson("/api/admin/stages/{$newStageId}")
            ->assertStatus(200)
            ->assertJson(['message' => 'Stage deleted']);
    }

    public function test_submissions_and_assessments_are_handled(): void
    {
        $submissionResponse = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/submissions', [
                'quest_task_id' => $this->task->id,
                'user_id' => $this->user->id,
                'answer_text' => 'My answer',
                'status' => 'submitted',
                'score' => 0,
            ]);

        $submissionResponse->assertStatus(201)
            ->assertJsonStructure(['data' => ['id', 'quest_task_id', 'user_id', 'status']]);

        $submissionId = $submissionResponse->json('data.id');

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/users/{$this->user->id}/progress")
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [['id', 'quest_task_id', 'user_id', 'status', 'task']]]);

        $this->actingAs($this->moderator, 'sanctum')
            ->postJson("/api/submissions/{$submissionId}/assessments", [
                'evaluator_id' => $this->moderator->id,
                'score' => 8,
                'comment' => 'Looks good',
                'rubric' => ['clarity' => 4],
            ])
            ->assertStatus(201)
            ->assertJsonStructure(['data' => ['id', 'task_submission_id', 'evaluator_id', 'score']]);
    }
}
