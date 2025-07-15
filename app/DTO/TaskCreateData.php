<?php

namespace App\DTO;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;

class TaskCreateData
{
    public int $user_id;
    public TaskStatus $status;
    public string $title;
    public ?string $description;
    public TaskPriority $priority;
    public ?int $parent_id;
    public ?int $assignee_id;

    public function __construct(
        int $user_id,
        string $title,
        ?string $description,
        TaskPriority $priority,
        ?int $parent_id = null,
        ?int $assignee_id = null,
        TaskStatus $status = TaskStatus::TODO,
    ) {
        $this->user_id = $user_id;
        $this->title = $title;
        $this->description = $description;
        $this->priority = $priority;
        $this->parent_id = $parent_id;
        $this->assignee_id = $assignee_id;
        $this->status = $status;
    }

    public static function fromArray(array $data, int $user_id): self
    {
        return new self(
            $user_id,
            $data['title'],
            $data['description'] ?? null,
            TaskPriority::from($data['priority']),
            $data['parent_id'] ?? null,
            $data['assignee_id'] ?? null,
        );
    }
}
