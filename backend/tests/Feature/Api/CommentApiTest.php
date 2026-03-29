<?php

namespace Tests\Feature\Api;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentApiTest extends TestCase
{
    use RefreshDatabase;

    private User $actor;
    private Post $post;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actor = User::factory()->create();
        $this->post  = Post::create([
            'user_id' => $this->actor->id,
            'title'   => 'Post for comments',
            'body'    => 'Post body.',
        ]);
    }

    private function createComment(array $attrs = []): Comment
    {
        return Comment::create(array_merge([
            'post_id' => $this->post->id,
            'name'    => 'Commenter',
            'email'   => 'commenter@example.com',
            'body'    => 'A comment body.',
        ], $attrs));
    }

    // ── Index ─────────────────────────────────────────────────────────────────

    public function test_index_returns_paginated_comments(): void
    {
        $this->createComment();

        $this->actingAs($this->actor, 'sanctum')
            ->getJson('/api/v1/comments')
            ->assertStatus(200)
            ->assertJsonStructure(['data', 'meta']);
    }

    public function test_index_requires_authentication(): void
    {
        $this->getJson('/api/v1/comments')->assertStatus(401);
    }

    // ── Show ──────────────────────────────────────────────────────────────────

    public function test_show_returns_comment(): void
    {
        $comment = $this->createComment();

        $this->actingAs($this->actor, 'sanctum')
            ->getJson("/api/v1/comments/{$comment->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.email', 'commenter@example.com');
    }

    public function test_show_returns_404_for_missing_comment(): void
    {
        $this->actingAs($this->actor, 'sanctum')
            ->getJson('/api/v1/comments/999999')
            ->assertStatus(404);
    }

    // ── Store ─────────────────────────────────────────────────────────────────

    public function test_store_creates_comment(): void
    {
        $this->actingAs($this->actor, 'sanctum')
            ->postJson('/api/v1/comments', [
                'post_id' => $this->post->id,
                'name'    => 'New Commenter',
                'email'   => 'new@example.com',
                'body'    => 'New comment body.',
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.email', 'new@example.com');

        $this->assertDatabaseHas('comments', ['email' => 'new@example.com']);
    }

    public function test_store_validates_required_fields(): void
    {
        $this->actingAs($this->actor, 'sanctum')
            ->postJson('/api/v1/comments', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['post_id', 'name', 'email', 'body']);
    }

    public function test_store_validates_post_id_exists(): void
    {
        $this->actingAs($this->actor, 'sanctum')
            ->postJson('/api/v1/comments', [
                'post_id' => 999999,
                'name'    => 'Commenter',
                'email'   => 'c@example.com',
                'body'    => 'Body.',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['post_id']);
    }

    public function test_store_requires_authentication(): void
    {
        $this->postJson('/api/v1/comments', [])->assertStatus(401);
    }

    // ── Update ────────────────────────────────────────────────────────────────

    public function test_update_modifies_comment(): void
    {
        $comment = $this->createComment();

        $this->actingAs($this->actor, 'sanctum')
            ->putJson("/api/v1/comments/{$comment->id}", [
                'post_id' => $this->post->id,
                'name'    => 'Updated Name',
                'email'   => 'updated@example.com',
                'body'    => 'Updated body.',
            ])
            ->assertStatus(200)
            ->assertJsonPath('data.name', 'Updated Name');

        $this->assertDatabaseHas('comments', ['id' => $comment->id, 'name' => 'Updated Name']);
    }

    public function test_update_requires_authentication(): void
    {
        $comment = $this->createComment();

        $this->putJson("/api/v1/comments/{$comment->id}", [])->assertStatus(401);
    }

    // ── Destroy ───────────────────────────────────────────────────────────────

    public function test_destroy_deletes_comment(): void
    {
        $comment = $this->createComment();

        $this->actingAs($this->actor, 'sanctum')
            ->deleteJson("/api/v1/comments/{$comment->id}")
            ->assertStatus(204);

        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    public function test_destroy_requires_authentication(): void
    {
        $comment = $this->createComment();

        $this->deleteJson("/api/v1/comments/{$comment->id}")->assertStatus(401);
    }
}
