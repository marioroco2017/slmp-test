<?php

namespace App\Services;

use App\Models\Post;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PostService
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Post::paginate($perPage);
    }

    public function create(User $user, array $data): Post
    {
        return $user->posts()->create($data);
    }

    public function update(Post $post, array $data): Post
    {
        $post->update($data);

        return $post;
    }

    public function delete(Post $post): void
    {
        $post->delete();
    }

    public function paginateComments(Post $post, int $perPage = 15): LengthAwarePaginator
    {
        return $post->comments()->paginate($perPage);
    }
}
