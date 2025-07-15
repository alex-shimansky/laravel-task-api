<?php

namespace App\DTO;

use App\Enums\TaskStatus;
use DateTimeInterface;

class TaskCompleteData extends TaskUpdateData
{
    public function __construct(DateTimeInterface $when)
    {
        parent::__construct(
            null,          // title
            null,          // description
            null,          // priority
            null           // assignee_id
        );

        $this->completed_at = $when;
        $this->status = TaskStatus::DONE;
    }

    public ?string $completed_at;
    public TaskStatus $status;
}
