<?php

namespace Tests\Feature\Api;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostApiTest extends TestCase
{
    use RefreshDatabase;

    private User $actor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actor = User::factory()->create();
    }

    private function createPost(array $attrs = []): Post
    {
        return Post::create(array_merge([
            'user_id' => $this->actor->id,
            'title'   => 'Sample Post',
            'body'    => 'Sample body.',
        ], $attrs));
    }

    // ── Index ─────────────────────────────────────────────────────────────────

    public function test_index_returns_paginated_posts(): void
    {
        $this->createPost();
        $this->createPost(['title' => 'Second Post']);

        $this->actingAs($this->actor, 'sanctum')
            ->getJson('/api/v1/posts')
            ->assertStatus(200)
            ->assertJsonStructure(['data', 'meta' => ['current_page', 'total']]);
    }

    public function test_index_requires_authentication(): void
    {
        $this->getJson('/api/v1/posts')->assertStatus(401);
    }

    // ── Show ──────────────────────────────────────────────────────────────────

    public function test_show_returns_post(): void
    {
        $post = $this->createPost();

        $this->actingAs($this->actor, 'sanctum')
            ->getJson("/api/v1/posts/{$post->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.title', 'Sample Post');
    }

    public function test_show_returns_404_for_missing_post(): void
    {
        $this->actingAs($this->actor, 'sanctum')
            ->getJson('/api/v1/posts/999999')
            ->assertStatus(404);
    }

    // ── Store ─────────────────────────────────────────────────────────────────

    public function test_store_creates_post(): void
    {
        $this->actingAs($this->actor, 'sanctum')
            ->postJson('/api/v1/posts', [
                'title' => 'New Post',
                'body'  => 'New post body.',
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.title', 'New Post');

        $this->assertDatabaseHas('posts', ['title' => 'New Post', 'user_id' => $this->actor->id]);
    }

    public function test_store_validates_required_fields(): void
    {
        $this->actingAs($this->actor, 'sanctum')
            ->postJson('/api/v1/posts', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'body']);
    }

    public function test_store_requires_authentication(): void
    {
        $this->postJson('/api/v1/posts', ['title' => 'x', 'body' => 'y'])->assertStatus(401);
    }

    // ── Update ────────────────────────────────────────────────────────────────

    public function test_update_modifies_post(): void
    {
        $post = $this->createPost();

        $this->actingAs($this->actor, 'sanctum')
            ->putJson("/api/v1/posts/{$post->id}", [
                'title' => 'Updated Title',
                'body'  => 'Updated body.',
            ])
            ->assertStatus(200)
            ->assertJsonPath('data.title', 'Updated Title');

        $this->assertDatabaseHas('posts', ['id' => $post->id, 'title' => 'Updated Title']);
    }

    public function test_update_requires_authentication(): void
    {
        $post = $this->createPost();

        $this->putJson("/api/v1/posts/{$post->id}", ['title' => 'x'])->assertStatus(401);
    }

    // ── Destroy ───────────────────────────────────────────────────────────────

    public function test_destroy_deletes_post(): void
    {
        $post = $this->createPost();

        $this->actingAs($this->actor, 'sanctum')
            ->deleteJson("/api/v1/posts/{$post->id}")
            ->assertStatus(204);

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    public function test_destroy_requires_authentication(): void
    {
        $post = $this->createPost();

        $this->deleteJson("/api/v1/posts/{$post->id}")->assertStatus(401);
    }

    // ── Nested relations ──────────────────────────────────────────────────────

    public function test_comments_returns_post_comments(): void
    {
        $post = $this->createPost();
        Comment::create([
            'post_id' => $post->id,
            'name'    => 'Commenter',
            'email'   => 'commenter@example.com',
            'body'    => 'A comment.',
        ]);

        $this->actingAs($this->actor, 'sanctum')
            ->getJson("/api/v1/posts/{$post->id}/comments")
            ->assertStatus(200)
            ->assertJsonStructure(['data']);
    }
}
