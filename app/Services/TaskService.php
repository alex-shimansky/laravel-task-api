<?php

namespace App\Services;

use App\DTO\TaskCreateData;
use App\DTO\TaskUpdateData;
use App\Enums\TaskStatus;
use App\Models\Task;
use App\Repositories\TaskRepositoryInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class TaskService
{
    public function __construct(
        private TaskRepositoryInterface $repository
    ) {}

    public function list(array $filters, array $sorts)
    {
        return $this->repository->getFiltered($filters, $sorts);
    }

    public function create(TaskCreateData $dto): Task
    {
        $data = [
            'user_id' => Auth::id(),
            'status' => TaskStatus::TODO,
            'title' => $dto->title,
            'description' => $dto->description,
            'priority' => $dto->priority->value,
            'parent_id' => $dto->parent_id ?? null,
            'assignee_id' => $dto->assignee_id,
        ];

        return $this->repository->create($data);
    }

    public function update(Task $task, TaskUpdateData $dto): Task
    {
        if ($task->status === TaskStatus::DONE) {
            throw ValidationException::withMessages(['task' => 'Cannot update completed task']);
        }

        $data = array_filter([
            'title' => $dto->title,
            'description' => $dto->description,
            'priority' => $dto->priority?->value,
            'assignee_id' => $dto->assignee_id,
        ], fn($v) => $v !== null);

        return $this->repository->update($task, $data);
    }

    public function delete(Task $task): void
    {
        if ($task->status === TaskStatus::DONE) {
            throw ValidationException::withMessages(['task' => 'Cannot delete completed task']);
        }

        $this->repository->delete($task);
    }

    public function markAsDone(Task $task): Task
    {
        if (!$task->allSubtasksDone()) {
            throw ValidationException::withMessages(['task' => 'All subtasks must be completed']);
        }

        return $this->repository->update($task, [
            'status' => TaskStatus::DONE,
            'completed_at' => Carbon::now(),
        ]);
    }
}
