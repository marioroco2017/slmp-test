<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Comment\StoreCommentRequest;
use App\Http\Requests\Comment\UpdateCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Services\CommentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Throwable;

class CommentController extends Controller
{
    public function __construct(private readonly CommentService $commentService)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Comment::class);

        return CommentResource::collection($this->commentService->paginate($request->user()));
    }

    public function show(Comment $comment): CommentResource
    {
        $this->authorize('view', $comment);

        return new CommentResource($comment);
    }

    public function store(StoreCommentRequest $request): JsonResponse
    {
        $this->authorize('create', Comment::class);

        try {
            $comment = $this->commentService->create($request->validated());

            return CommentResource::make($comment)->response()->setStatusCode(201);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Failed to create comment.',
                'error'   => app()->hasDebugModeEnabled() ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function update(UpdateCommentRequest $request, Comment $comment): JsonResponse
    {
        $this->authorize('update', $comment);

        try {
            $updated = $this->commentService->update($comment, $request->validated());

            return CommentResource::make($updated)->response();
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Failed to update comment.',
                'error'   => app()->hasDebugModeEnabled() ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function destroy(Comment $comment): JsonResponse
    {
        $this->authorize('delete', $comment);

        try {
            $this->commentService->delete($comment);

            return response()->json(null, 204);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Failed to delete comment.',
                'error'   => app()->hasDebugModeEnabled() ? $e->getMessage() : null,
            ], 500);
        }
    }
}
