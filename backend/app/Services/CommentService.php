<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CommentService
{
    public function paginate(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return Comment::whereHas('post', fn ($q) => $q->where('user_id', $user->id))->paginate($perPage);
    }

    public function create(array $data): Comment
    {
        return Comment::create($data);
    }

    public function update(Comment $comment, array $data): Comment
    {
        $comment->update($data);

        return $comment;
    }

    public function delete(Comment $comment): void
    {
        $comment->delete();
    }
}
