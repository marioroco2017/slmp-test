<?php

namespace Tests\Feature\Api;

use App\Models\Album;
use App\Models\Post;
use App\Models\Todo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserApiTest extends TestCase
{
    use RefreshDatabase;

    private User $actor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actor = User::factory()->create();
    }

    // ── Index ─────────────────────────────────────────────────────────────────

    public function test_index_returns_paginated_users(): void
    {
        User::factory()->count(3)->create();

        $this->actingAs($this->actor, 'sanctum')
            ->getJson('/api/v1/users')
            ->assertStatus(200)
            ->assertJsonStructure(['data', 'meta' => ['current_page', 'total']]);
    }

    public function test_index_requires_authentication(): void
    {
        $this->getJson('/api/v1/users')->assertStatus(401);
    }

    // ── Show ──────────────────────────────────────────────────────────────────

    public function test_show_returns_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($this->actor, 'sanctum')
            ->getJson("/api/v1/users/{$user->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.email', $user->email);
    }

    public function test_show_returns_404_for_missing_user(): void
    {
        $this->actingAs($this->actor, 'sanctum')
            ->getJson('/api/v1/users/999999')
            ->assertStatus(404);
    }

    // ── Store ─────────────────────────────────────────────────────────────────

    public function test_store_creates_user(): void
    {
        $response = $this->actingAs($this->actor, 'sanctum')
            ->postJson('/api/v1/users', [
                'name'     => 'Jane Doe',
                'username' => 'janedoe',
                'email'    => 'jane@example.com',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.email', 'jane@example.com');

        $this->assertDatabaseHas('users', ['email' => 'jane@example.com']);
    }

    public function test_store_validates_required_fields(): void
    {
        $this->actingAs($this->actor, 'sanctum')
            ->postJson('/api/v1/users', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'username', 'email']);
    }

    public function test_store_validates_unique_email(): void
    {
        $existing = User::factory()->create();

        $this->actingAs($this->actor, 'sanctum')
            ->postJson('/api/v1/users', [
                'name'     => 'Jane Doe',
                'username' => 'janedoe',
                'email'    => $existing->email,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_store_requires_authentication(): void
    {
        $this->postJson('/api/v1/users', [
            'name'     => 'Jane Doe',
            'username' => 'janedoe',
            'email'    => 'jane@example.com',
        ])->assertStatus(401);
    }

    // ── Update ────────────────────────────────────────────────────────────────

    public function test_update_modifies_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($this->actor, 'sanctum')
            ->putJson("/api/v1/users/{$user->id}", [
                'name'     => 'Updated Name',
                'username' => $user->username,
                'email'    => $user->email,
            ])
            ->assertStatus(200)
            ->assertJsonPath('data.name', 'Updated Name');

        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Updated Name']);
    }

    public function test_update_requires_authentication(): void
    {
        $user = User::factory()->create();

        $this->putJson("/api/v1/users/{$user->id}", ['name' => 'x'])->assertStatus(401);
    }

    // ── Destroy ───────────────────────────────────────────────────────────────

    public function test_destroy_deletes_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($this->actor, 'sanctum')
            ->deleteJson("/api/v1/users/{$user->id}")
            ->assertStatus(204);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_destroy_requires_authentication(): void
    {
        $user = User::factory()->create();

        $this->deleteJson("/api/v1/users/{$user->id}")->assertStatus(401);
    }

    // ── Nested relations ──────────────────────────────────────────────────────

    public function test_posts_returns_user_posts(): void
    {
        $user = User::factory()->create();
        Post::create(['user_id' => $user->id, 'title' => 'My Post', 'body' => 'body']);

        $this->actingAs($this->actor, 'sanctum')
            ->getJson("/api/v1/users/{$user->id}/posts")
            ->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_albums_returns_user_albums(): void
    {
        $user = User::factory()->create();
        Album::create(['user_id' => $user->id, 'title' => 'My Album']);

        $this->actingAs($this->actor, 'sanctum')
            ->getJson("/api/v1/users/{$user->id}/albums")
            ->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_todos_returns_user_todos(): void
    {
        $user = User::factory()->create();
        Todo::create(['user_id' => $user->id, 'title' => 'My Todo', 'completed' => false]);

        $this->actingAs($this->actor, 'sanctum')
            ->getJson("/api/v1/users/{$user->id}/todos")
            ->assertStatus(200)
            ->assertJsonStructure(['data']);
    }
}
