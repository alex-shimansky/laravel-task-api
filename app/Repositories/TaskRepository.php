<?php

namespace App\Repositories;

use App\DTO\TaskCreateData;
use App\DTO\TaskUpdateData;
use App\Models\Task;
use Illuminate\Support\Collection;

class TaskRepository implements TaskRepositoryInterface
{
    public function getFiltered(int $userId, array $filters, array $sorts): Collection
    {
        $query = Task::query()->where('user_id', $userId);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->whereFullText(['title', 'description'], $search, ['mode' => 'boolean']);
        }

        foreach ($sorts as $field => $dir) {
            $allowedFields = ['created_at', 'completed_at', 'priority'];
            $field = strtolower($field);
            $dir = strtolower($dir);

            if (in_array($field, $allowedFields) && in_array($dir, ['asc', 'desc'])) {
                $query->orderBy($field, $dir);
            }
        }

        $allTasks = $query->get();

        $tasksTree = $this->buildTree($allTasks);

        return collect($tasksTree);
    }

    /**
     * Recursive function to build a task tree from a flat list
     * @param Collection|array $tasks
     * @param int|null $parentId
     * @return array
     */
    protected function buildTree(Collection $tasks, ?int $parentId = null): array
    {
        $branch = [];

        foreach ($tasks as $task) {
            if ($task->parent_id === $parentId) {
                $children = $this->buildTree($tasks, $task->id);
                if ($children) {
                    $task->setRelation('subtasks', collect($children));
                } else {
                    $task->setRelation('subtasks', collect());
                }
                $branch[] = $task;
            }
        }

        return $branch;
    }

    public function create(TaskCreateData $dto): Task
    {
        return Task::create([
            'user_id'     => $dto->user_id,
            'status'      => $dto->status->value,
            'title'       => $dto->title,
            'description' => $dto->description,
            'priority'    => $dto->priority->value,
            'parent_id'   => $dto->parent_id,
            'assignee_id' => $dto->assignee_id,
        ]);
    }

    public function update(Task $task, TaskUpdateData $dto): Task
    {
        $task->update(array_filter([
            'title'       => $dto->title,
            'description' => $dto->description,
            'priority'    => $dto->priority?->value,
            'assignee_id' => $dto->assignee_id,
            'status'       => property_exists($dto, 'status') ? $dto->status?->value : null,
            'completed_at' => property_exists($dto, 'completed_at') ? $dto->completed_at : null,
        ], fn ($v) => $v !== null));

        return $task;
    }

    public function delete(Task $task): void
    {
        $task->delete();
    }
}
