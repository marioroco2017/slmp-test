<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Todo\StoreTodoRequest;
use App\Http\Requests\Todo\UpdateTodoRequest;
use App\Http\Resources\TodoResource;
use App\Models\Todo;
use App\Services\TodoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Throwable;

class TodoController extends Controller
{
    public function __construct(private readonly TodoService $todoService)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {

        $this->authorize('viewAny', Todo::class);

        $posts = $this->todoService->paginate($request->user());

        return TodoResource::collection($posts);


    }

    public function show(Todo $todo): TodoResource
    {
        $this->authorize('view', $todo);

        return new TodoResource($todo);
    }

    public function store(StoreTodoRequest $request): JsonResponse
    {
        $this->authorize('create', Todo::class);

        try {
            $todo = $this->todoService->create($request->user(), $request->validated());

            return TodoResource::make($todo)->response()->setStatusCode(201);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Failed to create todo.',
                'error' => app()->hasDebugModeEnabled() ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function update(UpdateTodoRequest $request, Todo $todo): JsonResponse
    {
        $this->authorize('update', $todo);

        try {
            $updated = $this->todoService->update($todo, $request->validated());

            return TodoResource::make($updated)->response();
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Failed to update todo.',
                'error' => app()->hasDebugModeEnabled() ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function destroy(Todo $todo): JsonResponse
    {
        $this->authorize('delete', $todo);

        try {
            $this->todoService->delete($todo);

            return response()->json(null, 204);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Failed to delete todo.',
                'error' => app()->hasDebugModeEnabled() ? $e->getMessage() : null,
            ], 500);
        }
    }
}
