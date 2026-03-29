<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class JsonPlaceHolderApi
{
    private string $baseUrl = 'https://jsonplaceholder.typicode.com';

    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function getUsers(): array
    {
        return $this->run('/users');
    }

    public function getPosts(): array
    {
        return $this->run('/posts');
    }

    public function getComments(): array
    {
        return $this->run('/comments');
    }

    public function getAlbums(): array
    {
        return $this->run('/albums');
    }

    public function getPhotos(): array
    {
        return $this->run('/photos');
    }

    public function getTodos(): array
    {
        return $this->run('/todos');
    }

    private function run(string $endpoint, string $method = 'GET', array $data = []): mixed
    {
        $url = rtrim($this->baseUrl, '/') . '/' . ltrim($endpoint, '/');

        $http = Http::retry(3, 500, throw: true)
            ->timeout(30)
            ->withHeaders([
                'Accept' => 'application/json',
                'User-Agent' => config('app.name'),
            ]);

        return match (strtoupper($method)) {
            'POST' => $http->post($url, $data)->throw()->json() ?? [],
            'PUT' => $http->put($url, $data)->throw()->json() ?? [],
            'PATCH' => $http->patch($url, $data)->throw()->json() ?? [],
            'DELETE' => $http->delete($url, $data)->throw()->json() ?? [],
            default => $http->get($url, $data ?: null)->throw()->json() ?? [],
        };




    }
}
