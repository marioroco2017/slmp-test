<?php

namespace Tests\Feature;

use App\Models\Album;
use App\Models\Comment;
use App\Models\Photo;
use App\Models\Post;
use App\Models\Todo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FetchDataCommandTest extends TestCase
{
    use RefreshDatabase;

    private function fakeApiResponses(): void
    {
        Http::fake([
            '*/users'    => Http::response($this->usersPayload(), 200),
            '*/posts'    => Http::response($this->postsPayload(), 200),
            '*/comments' => Http::response($this->commentsPayload(), 200),
            '*/albums'   => Http::response($this->albumsPayload(), 200),
            '*/photos'   => Http::response($this->photosPayload(), 200),
            '*/todos'    => Http::response($this->todosPayload(), 200),
        ]);
    }

    public function test_command_fetches_and_stores_all_resources(): void
    {
        $this->fakeApiResponses();

        $this->artisan('app:fetch-jsonplaceholder')->assertSuccessful();

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('posts', 1);
        $this->assertDatabaseCount('comments', 1);
        $this->assertDatabaseCount('albums', 1);
        $this->assertDatabaseCount('photos', 1);
        $this->assertDatabaseCount('todos', 1);

        $this->assertDatabaseHas('users', ['email' => 'leanne.graham@april.biz']);
        $this->assertDatabaseHas('posts', ['title' => 'Test Post Title']);
        $this->assertDatabaseHas('comments', ['email' => 'commenter@example.com']);
        $this->assertDatabaseHas('albums', ['title' => 'Test Album']);
        $this->assertDatabaseHas('photos', ['title' => 'Test Photo']);
        $this->assertDatabaseHas('todos', ['title' => 'Test Todo']);
    }

    public function test_command_is_idempotent(): void
    {
        $this->fakeApiResponses();
        $this->artisan('app:fetch-jsonplaceholder')->assertSuccessful();

        Http::fake([
            '*/users'    => Http::response($this->usersPayload(), 200),
            '*/posts'    => Http::response($this->postsPayload(), 200),
            '*/comments' => Http::response($this->commentsPayload(), 200),
            '*/albums'   => Http::response($this->albumsPayload(), 200),
            '*/photos'   => Http::response($this->photosPayload(), 200),
            '*/todos'    => Http::response($this->todosPayload(), 200),
        ]);
        $this->artisan('app:fetch-jsonplaceholder')->assertSuccessful();

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('posts', 1);
        $this->assertDatabaseCount('comments', 1);
        $this->assertDatabaseCount('albums', 1);
        $this->assertDatabaseCount('photos', 1);
        $this->assertDatabaseCount('todos', 1);
    }

    public function test_command_handles_http_error_gracefully(): void
    {
        Http::fake([
            '*/users' => Http::response([], 500),
            '*'       => Http::response([], 500),
        ]);

        $this->artisan('app:fetch-jsonplaceholder')->assertSuccessful();

        $this->assertDatabaseCount('users', 0);
    }

    // ── Payloads ──────────────────────────────────────────────────────────────

    private function usersPayload(): array
    {
        return [[
            'id'       => 1,
            'name'     => 'Leanne Graham',
            'username' => 'Bret',
            'email'    => 'Leanne.Graham@april.biz',
            'phone'    => '1-770-736-8031',
            'website'  => 'hildegard.org',
            'address'  => [
                'street'  => 'Kulas Light',
                'suite'   => 'Apt. 556',
                'city'    => 'Gwenborough',
                'zipcode' => '92998-3874',
                'geo'     => ['lat' => '-37.3159', 'lng' => '81.1496'],
            ],
            'company' => [
                'name'        => 'Romaguera-Crona',
                'catchPhrase' => 'Multi-layered client-server neural-net',
                'bs'          => 'harness real-time e-markets',
            ],
        ]];
    }

    private function postsPayload(): array
    {
        return [[
            'id'     => 1,
            'userId' => 1,
            'title'  => 'Test Post Title',
            'body'   => 'Test post body content.',
        ]];
    }

    private function commentsPayload(): array
    {
        return [[
            'id'     => 1,
            'postId' => 1,
            'name'   => 'Commenter Name',
            'email'  => 'commenter@example.com',
            'body'   => 'Comment body.',
        ]];
    }

    private function albumsPayload(): array
    {
        return [[
            'id'     => 1,
            'userId' => 1,
            'title'  => 'Test Album',
        ]];
    }

    private function photosPayload(): array
    {
        return [[
            'id'           => 1,
            'albumId'      => 1,
            'title'        => 'Test Photo',
            'url'          => 'https://via.placeholder.com/600/92c952',
            'thumbnailUrl' => 'https://via.placeholder.com/150/92c952',
        ]];
    }

    private function todosPayload(): array
    {
        return [[
            'id'        => 1,
            'userId'    => 1,
            'title'     => 'Test Todo',
            'completed' => false,
        ]];
    }
}
