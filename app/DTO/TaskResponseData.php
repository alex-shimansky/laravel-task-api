<?php

namespace App\DTO;

use App\Models\Task;
use App\Enums\TaskStatus;
use App\Enums\TaskPriority;

class TaskResponseData
{
    public int $id;
    public int $user_id;
    public ?int $assignee_id;
    public ?int $parent_id;
    public string $title;
    public ?string $description;
    public TaskStatus $status;
    public TaskPriority $priority;
    public ?string $completed_at;
    public string $created_at;
    /** @var TaskResponseData[] */
    public array $subtasks;

    public function __construct(array $data, array $subtasks = [])
    {
        $this->id = $data['id'];
        $this->user_id = $data['user_id'];
        $this->assignee_id = $data['assignee_id'] ?? null;
        $this->parent_id = $data['parent_id'] ?? null;
        $this->title = $data['title'];
        $this->description = $data['description'] ?? null;
        $this->status = TaskStatus::from($data['status']);
        $this->priority = TaskPriority::from($data['priority']);
        $this->completed_at = $data['completed_at'] ?? null;
        $this->created_at = $data['created_at'];

        $this->subtasks = array_map(
            fn($subtask) => new self($subtask->toArray(), $subtask->subtasks->all()),
            $subtasks
        );
    }

    public static function fromModel(Task $task): self
    {
        $task->loadMissing('subtasks');
        return new self($task->toArray(), $task->subtasks->all());
    }
}
