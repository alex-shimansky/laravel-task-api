<?php

namespace App\Services;

use App\DTO\TaskCompleteData;
use App\DTO\TaskCreateData;
use App\DTO\TaskUpdateData;
use App\Enums\TaskStatus;
use App\Models\Task;
use App\Repositories\TaskRepositoryInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class TaskService
{
    public function __construct(
        private TaskRepositoryInterface $repository
    ) {}

    public function list(int $userId, array $filters, array $sorts): Collection
    {
        return $this->repository->getFiltered($userId, $filters, $sorts);
    }

    public function create(TaskCreateData $dto): Task
    {
        return $this->repository->create($dto);
    }

    public function update(Task $task, TaskUpdateData $dto): Task
    {
        if ($task->status === TaskStatus::DONE) {
            throw ValidationException::withMessages(['task' => 'Cannot update completed task']);
        }

        return $this->repository->update($task, $dto);
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

        $dto = new TaskCompleteData(Carbon::now());

        return $this->repository->update($task, $dto);
    }
}
