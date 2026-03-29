<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Address;
use App\Models\Album;
use App\Models\Comment;
use App\Models\Company;
use App\Models\Geo;
use App\Models\Photo;
use App\Models\Post;
use App\Models\Todo;
use App\Models\User;
use App\Services\JsonPlaceHolderApi;
use Illuminate\Console\Command;


class FetchJsonPlaceholderData extends Command
{
    protected $signature = 'app:fetch-jsonplaceholder
                            {--chunk=500 : Batch size for bulk inserts}
                            {--retries=3 : HTTP retry attempts on failure}';

    protected $description = 'Fetch all JSONPlaceholder resources and upsert them into the database';

    public function __construct(private readonly JsonPlaceHolderApi $jsonPlaceHolderApi)
    {
        parent::__construct();
    }


    public function handle(): int
    {
        try {
            $chunk = max(1, (int) $this->option('chunk'));

            $this->info('Fetching users...');
            $this->upsertUsers();

            $this->info('Fetching posts...');
            $data = $this->jsonPlaceHolderApi->getPosts();
            $this->upsertSimple(Post::class, $data, ['id'], ['user_id', 'title', 'body'], $chunk);

            $this->info('Fetching comments...');
            $data = $this->jsonPlaceHolderApi->getComments();
            $this->upsertSimple(Comment::class, $data, ['id'], ['post_id', 'name', 'email', 'body'], $chunk);

            $this->info('Fetching Albums');
            $data = $this->jsonPlaceHolderApi->getAlbums();
            $this->upsertSimple(Album::class, $data, ['id'], ['user_id', 'title'], $chunk);

            $this->info('Fetching Photos');
            $data = $this->jsonPlaceHolderApi->getPhotos();
            $this->upsertSimple(Photo::class, $data, ['id'], ['album_id', 'title', 'url', 'thumbnail_url'], $chunk);

            $this->info('Fetching Todos');
            $data = $this->jsonPlaceHolderApi->getTodos();
            $this->upsertSimple(Todo::class, $data, ['id'], ['user_id', 'title', 'completed'], $chunk);

        } catch (\Exception $e) {
            $this->error('Failed to fetch' . $e->getMessage());
        }

        return self::SUCCESS;
    }


    private function upsertUsers(): void
    {
        $data = $this->jsonPlaceHolderApi->getUsers();
        foreach ($data as $raw) {
            // 1. User
            User::upsert(
                [
                    [
                        'id' => $raw['id'],
                        'name' => $raw['name'],
                        'username' => $raw['username'],
                        'email' => $raw['email'],
                        'phone' => $raw['phone'] ?? null,
                        'website' => $raw['website'] ?? null,
                        'password' => bcrypt('password123'),
                    ]
                ],
                uniqueBy: ['id'],
                update: ['name', 'username', 'email', 'phone', 'website'],
            );

            // 2. Address
            $addr = $raw['address'];
            Address::upsert(
                [['user_id' => $raw['id'], 'street' => $addr['street'] ?? null, 'suite' => $addr['suite'] ?? null, 'city' => $addr['city'] ?? null, 'zipcode' => $addr['zipcode'] ?? null]],
                uniqueBy: ['user_id'],
                update: ['street', 'suite', 'city', 'zipcode'],
            );

            // 3. Geo (requires address PK — fetch after upsert)
            $addressId = Address::where('user_id', $raw['id'])->value('id');
            $geo = $addr['geo'];
            Geo::upsert(
                [['address_id' => $addressId, 'lat' => $geo['lat'] ?? null, 'lng' => $geo['lng'] ?? null]],
                uniqueBy: ['address_id'],
                update: ['lat', 'lng'],
            );

            // 4. Company
            $company = $raw['company'];
            Company::upsert(
                [['user_id' => $raw['id'], 'name' => $company['name'] ?? '', 'catch_phrase' => $company['catchPhrase'] ?? null, 'bs' => $company['bs'] ?? null]],
                uniqueBy: ['user_id'],
                update: ['name', 'catch_phrase', 'bs'],
            );
        }
    }

    /**
     * Generic chunked upsert for flat resources (posts, comments, albums, photos, todos).
     *
     * @param  class-string  $model
     * @param  array<int, array<string, mixed>>  $rows
     * @param  string[]  $uniqueBy
     * @param  string[]  $updateColumns
     */
    private function upsertSimple(string $model, array $rows, array $uniqueBy, array $updateColumns, int $chunk): void
    {
        $now = now();

        $mapped = array_map(function (array $row) use ($now): array {
            return $this->mapColumns($row) + ['created_at' => $now, 'updated_at' => $now];
        }, $rows);

        foreach (array_chunk($mapped, $chunk) as $batch) {
            $model::upsert($batch, uniqueBy: $uniqueBy, update: array_merge($updateColumns, ['updated_at']));

        }
    }

    /**
     * Map API JSON keys to database column names.
     * Only renames keys that differ; all others are passed through as-is.
     */
    private function mapColumns(array $row): array
    {
        $renames = [
            'postId' => 'post_id',
            'userId' => 'user_id',
            'albumId' => 'album_id',
            'thumbnailUrl' => 'thumbnail_url',
        ];

        $result = [];
        foreach ($row as $key => $value) {
            $result[$renames[$key] ?? $key] = $value;
        }

        return $result;
    }
}
