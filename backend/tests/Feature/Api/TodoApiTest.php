<?php

namespace Tests\Feature\Api;

use App\Models\Todo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TodoApiTest extends TestCase
{
    use RefreshDatabase;

    private User $actor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actor = User::factory()->create();
    }

    private function createTodo(array $attrs = []): Todo
    {
        return Todo::create(array_merge([
            'user_id'   => $this->actor->id,
            'title'     => 'Sample Todo',
            'completed' => false,
        ], $attrs));
    }

    // ── Index ─────────────────────────────────────────────────────────────────

    public function test_index_returns_paginated_todos(): void
    {
        $this->createTodo();

        $this->actingAs($this->actor, 'sanctum')
            ->getJson('/api/v1/todos')
            ->assertStatus(200)
            ->assertJsonStructure(['data', 'meta']);
    }

    public function test_index_requires_authentication(): void
    {
        $this->getJson('/api/v1/todos')->assertStatus(401);
    }

    // ── Show ──────────────────────────────────────────────────────────────────

    public function test_show_returns_todo(): void
    {
        $todo = $this->createTodo();

        $this->actingAs($this->actor, 'sanctum')
            ->getJson("/api/v1/todos/{$todo->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.title', 'Sample Todo')
            ->assertJsonPath('data.completed', false);
    }

    public function test_show_returns_404_for_missing_todo(): void
    {
        $this->actingAs($this->actor, 'sanctum')
            ->getJson('/api/v1/todos/999999')
            ->assertStatus(404);
    }

    // ── Store ─────────────────────────────────────────────────────────────────

    public function test_store_creates_todo(): void
    {
        $this->actingAs($this->actor, 'sanctum')
            ->postJson('/api/v1/todos', [
                'title'     => 'New Todo',
                'completed' => false,
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.title', 'New Todo');

        $this->assertDatabaseHas('todos', ['title' => 'New Todo', 'user_id' => $this->actor->id]);
    }

    public function test_store_creates_completed_todo(): void
    {
        $this->actingAs($this->actor, 'sanctum')
            ->postJson('/api/v1/todos', [
                'title'     => 'Done Todo',
                'completed' => true,
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.completed', true);
    }

    public function test_store_validates_required_title(): void
    {
        $this->actingAs($this->actor, 'sanctum')
            ->postJson('/api/v1/todos', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    public function test_store_requires_authentication(): void
    {
        $this->postJson('/api/v1/todos', ['title' => 'x'])->assertStatus(401);
    }

    // ── Update ────────────────────────────────────────────────────────────────

    public function test_update_modifies_todo(): void
    {
        $todo = $this->createTodo();

        $this->actingAs($this->actor, 'sanctum')
            ->putJson("/api/v1/todos/{$todo->id}", [
                'title'     => 'Updated Todo',
                'completed' => true,
            ])
            ->assertStatus(200)
            ->assertJsonPath('data.title', 'Updated Todo')
            ->assertJsonPath('data.completed', true);

        $this->assertDatabaseHas('todos', ['id' => $todo->id, 'title' => 'Updated Todo', 'completed' => 1]);
    }

    public function test_update_requires_authentication(): void
    {
        $todo = $this->createTodo();

        $this->putJson("/api/v1/todos/{$todo->id}", [])->assertStatus(401);
    }

    // ── Destroy ───────────────────────────────────────────────────────────────

    public function test_destroy_deletes_todo(): void
    {
        $todo = $this->createTodo();

        $this->actingAs($this->actor, 'sanctum')
            ->deleteJson("/api/v1/todos/{$todo->id}")
            ->assertStatus(204);

        $this->assertDatabaseMissing('todos', ['id' => $todo->id]);
    }

    public function test_destroy_requires_authentication(): void
    {
        $todo = $this->createTodo();

        $this->deleteJson("/api/v1/todos/{$todo->id}")->assertStatus(401);
    }
}
