<?php

namespace App\Repositories;

use App\DTO\TaskCreateData;
use App\DTO\TaskUpdateData;
use App\Models\Task;
use Illuminate\Support\Collection;

interface TaskRepositoryInterface
{
    public function getFiltered(int $userId, array $filters, array $sorts): Collection;

    public function create(TaskCreateData $dto): Task;

    public function update(Task $task, TaskUpdateData $dto): Task;

    public function delete(Task $task): void;
}
