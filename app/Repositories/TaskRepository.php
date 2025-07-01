<?php

namespace App\Repositories;

use App\Models\Task;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class TaskRepository implements TaskRepositoryInterface
{
    public function getFiltered(array $filters, array $sorts): Collection
    {
        $query = Task::query()->where('user_id', Auth::id());

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
    protected function buildTree($tasks, ?int $parentId = null): array
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

    public function create(array $data): Task
    {
        return Task::create($data);
    }

    public function update(Task $task, array $data): Task
    {
        $task->update($data);
        return $task;
    }

    public function delete(Task $task): void
    {
        $task->delete();
    }
}
