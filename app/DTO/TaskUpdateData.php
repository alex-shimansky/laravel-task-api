<?php

namespace App\DTO;

use App\Enums\TaskPriority;

class TaskUpdateData
{
    public ?string $title;
    public ?string $description;
    public ?TaskPriority $priority;
    public ?int $assignee_id;

    public function __construct(
        ?string $title = null,
        ?string $description = null,
        ?TaskPriority $priority = null,
        ?int $assignee_id = null,
    ) {
        $this->title = $title;
        $this->description = $description;
        $this->priority = $priority;
        $this->assignee_id = $assignee_id;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['title'] ?? null,
            $data['description'] ?? null,
            isset($data['priority']) ? TaskPriority::from($data['priority']) : null,
            $data['assignee_id'] ?? null,
        );
    }
}
