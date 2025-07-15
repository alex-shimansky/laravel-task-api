<?php

namespace App\Http\Controllers\Api;

use App\DTO\TaskCreateData;
use App\DTO\TaskUpdateData;
use App\DTO\TaskResponseData;
use App\Http\Controllers\Controller;
use App\Http\Requests\ListTasksRequest;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

/**
 * @OA\Tag(
 *     name="Tasks",
 *     description="Task management endpoints"
 * )
 */
class TaskController extends Controller
{
    use AuthorizesRequests;

    /**
     * TaskController constructor.
     *
     * @param TaskService $taskService
     */
    public function __construct(private TaskService $taskService)
    {
    }

    /**
     * List tasks with filters and sorting.
     *
     * @param ListTasksRequest $request
     * @return JsonResponse
     *
     * @OA\Get(
     *     path="/api/tasks",
     *     summary="List tasks with filters and sorting",
     *     tags={"Tasks"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         @OA\Schema(type="string"),
     *         description="Filter by status"
     *     ),
     *     @OA\Parameter(
     *         name="priority",
     *         in="query",
     *         @OA\Schema(type="integer"),
     *         description="Filter by priority"
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         @OA\Schema(type="string"),
     *         description="Search by keyword"
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         @OA\Schema(type="string", example="priority:desc,created_at:asc"),
     *         description="Sort by multiple fields"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of tasks",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Task"))
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function index(ListTasksRequest $request): JsonResponse
    {
        $tasks = $this->taskService->list(
            $request->user()->id,
            $request->only(['status', 'priority', 'search']),
            $request->sorts(),
        );

        return response()->json($this->mapTasksToDto($tasks));
    }

    /**
     * Recursively map tasks and their subtasks to DTOs.
     *
     * @param Collection<Task> $tasks
     * @return Collection<TaskResponseData>
     */
    private function mapTasksToDto(Collection $tasks): Collection
    {
        return $tasks->map(function (Task $task) {
            $dto = TaskResponseData::fromModel($task);
            if ($task->relationLoaded('subtasks') && $task->subtasks->isNotEmpty()) {
                $dto->subtasks = $this->mapTasksToDto($task->subtasks)->all();
            }
            return $dto;
        });
    }

    /**
     * Create a new task.
     *
     * @param StoreTaskRequest $request
     * @return JsonResponse
     *
     * @OA\Post(
     *     path="/api/tasks",
     *     summary="Create a new task",
     *     tags={"Tasks"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title", "priority"},
     *             @OA\Property(property="title", type="string", example="Buy groceries"),
     *             @OA\Property(property="description", type="string", example="Milk, eggs, bread"),
     *             @OA\Property(property="priority", type="integer", example=2),
     *             @OA\Property(property="parent_id", type="integer", nullable=true),
     *             @OA\Property(property="assignee_id", type="integer", nullable=true)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Task created", @OA\JsonContent(ref="#/components/schemas/Task")),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function store(StoreTaskRequest $request): JsonResponse
    {
        $dto = TaskCreateData::fromArray($request->validated(), $request->user()->id);
        $task = $this->taskService->create($dto);

        return response()->json(TaskResponseData::fromModel($task), 201);
    }

    /**
     * Show a specific task.
     *
     * @param Task $task
     * @return JsonResponse
     *
     * @throws AuthorizationException
     * @OA\Get(
     *     path="/api/tasks/{id}",
     *     summary="Get task by ID",
     *     tags={"Tasks"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Task details", @OA\JsonContent(ref="#/components/schemas/Task")),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function show(Task $task): JsonResponse
    {
        $this->authorize('view', $task);
        return response()->json($task->load('subtasks'));
    }

    /**
     * Update a task.
     *
     * @param UpdateTaskRequest $request
     * @param Task $task
     * @return JsonResponse
     *
     * @throws AuthorizationException
     * @throws ValidationException
     * @OA\Put(
     *     path="/api/tasks/{id}",
     *     summary="Update task",
     *     tags={"Tasks"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="New title"),
     *             @OA\Property(property="description", type="string", example="Updated description"),
     *             @OA\Property(property="priority", type="integer", example=3),
     *             @OA\Property(property="assignee_id", type="integer", nullable=true)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Task updated", @OA\JsonContent(ref="#/components/schemas/Task")),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        $this->authorize('update', $task);

        $dto = TaskUpdateData::fromArray($request->validated());
        $task = $this->taskService->update($task, $dto);

        return response()->json(TaskResponseData::fromModel($task));
    }

    /**
     * Delete a task.
     *
     * @param Task $task
     * @return JsonResponse
     *
     * @throws AuthorizationException
     * @throws ValidationException
     * @OA\Delete(
     *     path="/api/tasks/{id}",
     *     summary="Delete task",
     *     tags={"Tasks"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Task deleted"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function destroy(Task $task): JsonResponse
    {
        $this->authorize('delete', $task);

        $this->taskService->delete($task);

        return response()->json(['message' => 'Deleted']);
    }

    /**
     * Mark task as done.
     *
     * @param Task $task
     * @return JsonResponse
     *
     * @throws AuthorizationException
     * @throws ValidationException
     * @OA\Post(
     *     path="/api/tasks/{id}/done",
     *     summary="Mark task as done",
     *     tags={"Tasks"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Task marked as done", @OA\JsonContent(ref="#/components/schemas/Task")),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=422, description="Validation error (if subtasks not done)")
     * )
     */
    public function markAsDone(Task $task): JsonResponse
    {
        $this->authorize('update', $task);

        $task = $this->taskService->markAsDone($task);

        return response()->json(TaskResponseData::fromModel($task));
    }
}
