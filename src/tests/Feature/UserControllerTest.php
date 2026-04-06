<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Создаем админа и обычного пользователя для тестов
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->user = User::factory()->create(['role' => 'user']);
    }

    public function test_admin_can_list_users()
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/admin/users');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'email', 'role']
                ]
            ]);
    }

    public function test_user_cannot_list_users()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/admin/users');

        $response->assertStatus(403);
    }

    public function test_admin_can_create_user()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'role' => 'user'
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/admin/users', $userData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['id', 'name', 'email', 'role']
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'role' => 'user'
        ]);
    }

    public function test_admin_can_show_user()
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson("/api/admin/users/{$this->user->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                    'role' => $this->user->role
                ]
            ]);
    }

    public function test_admin_can_update_user()
    {
        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'role' => 'admin'
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/admin/users/{$this->user->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $this->user->id,
                    'name' => 'Updated Name',
                    'email' => 'updated@example.com',
                    'role' => 'admin'
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'role' => 'admin'
        ]);
    }

    public function test_admin_can_delete_user()
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->deleteJson("/api/admin/users/{$this->user->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'User deleted successfully']);

        $this->assertDatabaseMissing('users', ['id' => $this->user->id]);
    }
}
