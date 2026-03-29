<?php

namespace Tests\Feature\Api;

use App\Models\Album;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhotoApiTest extends TestCase
{
    use RefreshDatabase;

    private User $actor;
    private Album $album;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actor = User::factory()->create();
        $this->album = Album::create([
            'user_id' => $this->actor->id,
            'title'   => 'Test Album',
        ]);
    }

    private function createPhoto(array $attrs = []): Photo
    {
        return Photo::create(array_merge([
            'album_id'      => $this->album->id,
            'title'         => 'Sample Photo',
            'url'           => 'https://example.com/photo.jpg',
            'thumbnail_url' => 'https://example.com/thumb.jpg',
        ], $attrs));
    }

    // ── Index ─────────────────────────────────────────────────────────────────

    public function test_index_returns_paginated_photos(): void
    {
        $this->createPhoto();

        $this->actingAs($this->actor, 'sanctum')
            ->getJson('/api/v1/photos')
            ->assertStatus(200)
            ->assertJsonStructure(['data', 'meta']);
    }

    public function test_index_requires_authentication(): void
    {
        $this->getJson('/api/v1/photos')->assertStatus(401);
    }

    // ── Show ──────────────────────────────────────────────────────────────────

    public function test_show_returns_photo(): void
    {
        $photo = $this->createPhoto();

        $this->actingAs($this->actor, 'sanctum')
            ->getJson("/api/v1/photos/{$photo->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.title', 'Sample Photo');
    }

    public function test_show_returns_404_for_missing_photo(): void
    {
        $this->actingAs($this->actor, 'sanctum')
            ->getJson('/api/v1/photos/999999')
            ->assertStatus(404);
    }

    // ── Store ─────────────────────────────────────────────────────────────────

    public function test_store_creates_photo(): void
    {
        $this->actingAs($this->actor, 'sanctum')
            ->postJson('/api/v1/photos', [
                'album_id'      => $this->album->id,
                'title'         => 'New Photo',
                'url'           => 'https://example.com/new.jpg',
                'thumbnail_url' => 'https://example.com/new-thumb.jpg',
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.title', 'New Photo');

        $this->assertDatabaseHas('photos', ['title' => 'New Photo']);
    }

    public function test_store_validates_required_fields(): void
    {
        $this->actingAs($this->actor, 'sanctum')
            ->postJson('/api/v1/photos', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['album_id', 'title', 'url', 'thumbnail_url']);
    }

    public function test_store_validates_album_id_exists(): void
    {
        $this->actingAs($this->actor, 'sanctum')
            ->postJson('/api/v1/photos', [
                'album_id'      => 999999,
                'title'         => 'Photo',
                'url'           => 'https://example.com/photo.jpg',
                'thumbnail_url' => 'https://example.com/thumb.jpg',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['album_id']);
    }

    public function test_store_validates_url_format(): void
    {
        $this->actingAs($this->actor, 'sanctum')
            ->postJson('/api/v1/photos', [
                'album_id'      => $this->album->id,
                'title'         => 'Photo',
                'url'           => 'not-a-url',
                'thumbnail_url' => 'also-not-a-url',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['url', 'thumbnail_url']);
    }

    public function test_store_requires_authentication(): void
    {
        $this->postJson('/api/v1/photos', [])->assertStatus(401);
    }

    // ── Update ────────────────────────────────────────────────────────────────

    public function test_update_modifies_photo(): void
    {
        $photo = $this->createPhoto();

        $this->actingAs($this->actor, 'sanctum')
            ->putJson("/api/v1/photos/{$photo->id}", [
                'album_id'      => $this->album->id,
                'title'         => 'Updated Photo',
                'url'           => 'https://example.com/updated.jpg',
                'thumbnail_url' => 'https://example.com/updated-thumb.jpg',
            ])
            ->assertStatus(200)
            ->assertJsonPath('data.title', 'Updated Photo');

        $this->assertDatabaseHas('photos', ['id' => $photo->id, 'title' => 'Updated Photo']);
    }

    public function test_update_requires_authentication(): void
    {
        $photo = $this->createPhoto();

        $this->putJson("/api/v1/photos/{$photo->id}", [])->assertStatus(401);
    }

    // ── Destroy ───────────────────────────────────────────────────────────────

    public function test_destroy_deletes_photo(): void
    {
        $photo = $this->createPhoto();

        $this->actingAs($this->actor, 'sanctum')
            ->deleteJson("/api/v1/photos/{$photo->id}")
            ->assertStatus(204);

        $this->assertDatabaseMissing('photos', ['id' => $photo->id]);
    }

    public function test_destroy_requires_authentication(): void
    {
        $photo = $this->createPhoto();

        $this->deleteJson("/api/v1/photos/{$photo->id}")->assertStatus(401);
    }
}
