<?php

namespace App\Http\Controllers\Api;

use App\DTO\TaskCreateData;
use App\DTO\TaskUpdateData;
use App\DTO\TaskResponseData;
use App\Enums\TaskStatus;
use App\Enums\TaskPriority;
use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

/**
 * @OA\Tag(name="Tasks", description="Task management endpoints")
 */
class TaskController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private TaskService $taskService)
    {
    }

    /**
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
     *         @OA\Schema(
     *             type="string",
     *             example="priority:desc,created_at:asc"
     *         ),
     *         description="Sort by multiple fields. Format: field:asc|desc,field2:asc|desc"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of tasks",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Task"))
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function index(Request $request)
    {
        $request->validate([
            'status' => ['nullable', new Enum(TaskStatus::class)],
            'priority' => ['nullable', new Enum(TaskPriority::class)],
        ]);

        $filters = $request->only(['status', 'priority', 'search']);
        $sorts = [];

        if ($request->filled('sort')) {
            foreach (explode(',', $request->sort) as $sortParam) {
                [$field, $dir] = explode(':', $sortParam) + [null, 'asc'];
                $dir = strtolower($dir);
                if (in_array($field, ['created_at', 'completed_at', 'priority']) && in_array($dir, ['asc', 'desc'])) {
                    $sorts[$field] = $dir;
                }
            }
        }

        $tasks = $this->taskService->list($filters, $sorts);
        $tasksDto = $this->mapTasksToDto($tasks);
        return response()->json($tasksDto);
    }

    private function mapTasksToDto($tasks)
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
    public function store(Request $request)
    {
        $data = $request->all();

        if (isset($data['parent_id']) && $data['parent_id'] === 0) {
            $data['parent_id'] = null;
        }

        $validated = validator($data, [
            'title' => 'required|string',
            'description' => 'nullable|string',
            'priority' => ['required', new Enum(TaskPriority::class)],
            'parent_id' => 'nullable|exists:tasks,id',
            'assignee_id' => 'nullable|exists:users,id',
        ])->validate();

        $dto = TaskCreateData::fromArray($validated);
        $task = $this->taskService->create($dto);

        return response()->json(TaskResponseData::fromModel($task), 201);
    }

    /**
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
    public function show(Task $task)
    {
        $this->authorize('view', $task);
        return response()->json($task->load('subtasks'));
    }

    /**
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
    public function update(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $validated = $request->validate([
            'title' => 'sometimes|string',
            'description' => 'nullable|string',
            'priority' => ['nullable', new Enum(TaskPriority::class)],
            'assignee_id' => 'nullable|exists:users,id',
        ]);

        $dto = TaskUpdateData::fromArray($validated);
        $task = $this->taskService->update($task, $dto);

        return response()->json(TaskResponseData::fromModel($task));
    }

    /**
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
    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);

        $this->taskService->delete($task);

        return response()->json(['message' => 'Deleted']);
    }

    /**
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
    public function markAsDone(Task $task)
    {
        $this->authorize('update', $task);

        $task = $this->taskService->markAsDone($task);

        return response()->json(TaskResponseData::fromModel($task));
    }
}
