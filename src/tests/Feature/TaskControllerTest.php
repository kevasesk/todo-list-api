<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TaskControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    public function testIndex()
    {
        Task::factory()->count(5)->create(['user_id' => $this->user->id]);

        $response = $this->getJson('/api/tasks');

        $response->assertStatus(200)
            ->assertJsonCount(5);
    }

    public function testIndexWithFilters()
    {
        Task::factory()->create([
            'user_id' => $this->user->id,
            'status' => TaskStatus::Todo->value,
            'priority' => 3,
            'title' => 'Test Task',
        ]);

        $response = $this->getJson('/api/tasks?status=todo&priority=3&title=Test');

        $response->assertStatus(200)
            ->assertJsonCount(1);
    }

    public function testStore()
    {
        $data = [
            'title' => 'New Task',
            'description' => 'Task description',
            'status' => 'todo',
            'priority' => 4,
        ];

        $response = $this->postJson('/api/tasks', $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['title' => 'New Task']);
    }

    public function testStoreValidation()
    {
        $response = $this->postJson('/api/tasks', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    public function testShow()
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $response = $this->getJson("/api/tasks/{$task->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $task->id]);
    }

    public function testUpdate()
    {
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'status' => TaskStatus::Todo->value,
        ]);

        $data = [
            'title' => 'Updated Task',
            'description' => 'Updated description',
            'priority' => 2,
        ];

        $response = $this->putJson("/api/tasks/{$task->id}", $data);

        $response->assertStatus(200)
            ->assertJsonFragment(['title' => 'Updated Task']);
    }

    public function testUpdateCompletedTask()
    {
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'status' => TaskStatus::Done->value,
        ]);

        $data = [
            'title' => 'Trying to update',
        ];

        $response = $this->putJson("/api/tasks/{$task->id}", $data);

        $response->assertStatus(400)
            ->assertJson(['message' => 'You can\'t change a task that has already been completed']);
    }

    public function testComplete()
    {
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'status' => TaskStatus::Todo->value,
        ]);

        $response = $this->patchJson("/api/tasks/{$task->id}/complete", ['status' => true]);

        $response->assertStatus(200)
            ->assertJsonFragment(['status' => TaskStatus::Done->value]);
    }

    public function testCompleteWithUndoneChildren()
    {
        $parentTask = Task::factory()->create([
            'user_id' => $this->user->id,
            'status' => TaskStatus::Todo->value,
        ]);

        Task::factory()->create([
            'user_id' => $this->user->id,
            'parent_id' => $parentTask->id,
            'status' => TaskStatus::Todo->value,
        ]);

        $response = $this->patchJson("/api/tasks/{$parentTask->id}/complete", ['status' => true]);

        $response->assertStatus(400)
            ->assertJson(['message' => 'Cannot complete task: Some child tasks are not done!']);
    }

    public function testDestroy()
    {
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'status' => TaskStatus::Todo->value,
        ]);

        $response = $this->deleteJson("/api/tasks/{$task->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'The task was successfully deleted.']);
    }

    public function testDestroyCompletedTask()
    {
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'status' => TaskStatus::Done->value,
        ]);

        $response = $this->deleteJson("/api/tasks/{$task->id}");

        $response->assertStatus(400)
            ->assertJson(['message' => 'You cannot delete a completed task.']);
    }
}
