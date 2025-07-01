<?php

namespace App\DTO;

use App\Enums\TaskPriority;

class TaskCreateData
{
    public string $title;
    public ?string $description;
    public TaskPriority $priority;
    public ?int $parent_id;
    public ?int $assignee_id;

    public function __construct(
        string $title,
        ?string $description,
        TaskPriority $priority,
        ?int $parent_id = null,
        ?int $assignee_id = null,
    ) {
        $this->title = $title;
        $this->description = $description;
        $this->priority = $priority;
        $this->parent_id = $parent_id;
        $this->assignee_id = $assignee_id;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['title'],
            $data['description'] ?? null,
            TaskPriority::from($data['priority']),
            $data['parent_id'] ?? null,
            $data['assignee_id'] ?? null,
        );
    }
}
