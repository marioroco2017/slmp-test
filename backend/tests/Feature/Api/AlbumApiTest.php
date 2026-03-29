<?php

namespace Tests\Feature\Api;

use App\Models\Album;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlbumApiTest extends TestCase
{
    use RefreshDatabase;

    private User $actor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actor = User::factory()->create();
    }

    private function createAlbum(array $attrs = []): Album
    {
        return Album::create(array_merge([
            'user_id' => $this->actor->id,
            'title'   => 'Sample Album',
        ], $attrs));
    }

    // ── Index ─────────────────────────────────────────────────────────────────

    public function test_index_returns_paginated_albums(): void
    {
        $this->createAlbum();

        $this->actingAs($this->actor, 'sanctum')
            ->getJson('/api/v1/albums')
            ->assertStatus(200)
            ->assertJsonStructure(['data', 'meta']);
    }

    public function test_index_requires_authentication(): void
    {
        $this->getJson('/api/v1/albums')->assertStatus(401);
    }

    // ── Show ──────────────────────────────────────────────────────────────────

    public function test_show_returns_album(): void
    {
        $album = $this->createAlbum();

        $this->actingAs($this->actor, 'sanctum')
            ->getJson("/api/v1/albums/{$album->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.title', 'Sample Album');
    }

    public function test_show_returns_404_for_missing_album(): void
    {
        $this->actingAs($this->actor, 'sanctum')
            ->getJson('/api/v1/albums/999999')
            ->assertStatus(404);
    }

    // ── Store ─────────────────────────────────────────────────────────────────

    public function test_store_creates_album(): void
    {
        $this->actingAs($this->actor, 'sanctum')
            ->postJson('/api/v1/albums', ['title' => 'New Album'])
            ->assertStatus(201)
            ->assertJsonPath('data.title', 'New Album');

        $this->assertDatabaseHas('albums', ['title' => 'New Album', 'user_id' => $this->actor->id]);
    }

    public function test_store_validates_required_fields(): void
    {
        $this->actingAs($this->actor, 'sanctum')
            ->postJson('/api/v1/albums', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    public function test_store_requires_authentication(): void
    {
        $this->postJson('/api/v1/albums', ['title' => 'x'])->assertStatus(401);
    }

    // ── Update ────────────────────────────────────────────────────────────────

    public function test_update_modifies_album(): void
    {
        $album = $this->createAlbum();

        $this->actingAs($this->actor, 'sanctum')
            ->putJson("/api/v1/albums/{$album->id}", ['title' => 'Updated Album'])
            ->assertStatus(200)
            ->assertJsonPath('data.title', 'Updated Album');

        $this->assertDatabaseHas('albums', ['id' => $album->id, 'title' => 'Updated Album']);
    }

    public function test_update_requires_authentication(): void
    {
        $album = $this->createAlbum();

        $this->putJson("/api/v1/albums/{$album->id}", ['title' => 'x'])->assertStatus(401);
    }

    // ── Destroy ───────────────────────────────────────────────────────────────

    public function test_destroy_deletes_album(): void
    {
        $album = $this->createAlbum();

        $this->actingAs($this->actor, 'sanctum')
            ->deleteJson("/api/v1/albums/{$album->id}")
            ->assertStatus(204);

        $this->assertDatabaseMissing('albums', ['id' => $album->id]);
    }

    public function test_destroy_requires_authentication(): void
    {
        $album = $this->createAlbum();

        $this->deleteJson("/api/v1/albums/{$album->id}")->assertStatus(401);
    }

    // ── Nested relations ──────────────────────────────────────────────────────

    public function test_photos_returns_album_photos(): void
    {
        $album = $this->createAlbum();
        Photo::create([
            'album_id'      => $album->id,
            'title'         => 'My Photo',
            'url'           => 'https://example.com/photo.jpg',
            'thumbnail_url' => 'https://example.com/thumb.jpg',
        ]);

        $this->actingAs($this->actor, 'sanctum')
            ->getJson("/api/v1/albums/{$album->id}/photos")
            ->assertStatus(200)
            ->assertJsonStructure(['data']);
    }
}
