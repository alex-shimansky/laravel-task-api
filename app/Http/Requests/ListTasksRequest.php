<?php

namespace App\Http\Requests;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class ListTasksRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status'   => ['nullable', new Enum(TaskStatus::class)],
            'priority' => ['nullable', new Enum(TaskPriority::class)],
            'search'   => ['nullable', 'string'],
            'sort'     => ['nullable', 'string'],
        ];
    }

    public function sorts(): array
    {
        $sorts = [];

        if ($this->filled('sort')) {
            foreach (explode(',', $this->sort) as $sortParam) {
                [$field, $dir] = explode(':', $sortParam) + [null, 'asc'];
                $dir = strtolower($dir);
                if (in_array($field, ['created_at', 'completed_at', 'priority']) && in_array($dir, ['asc', 'desc'])) {
                    $sorts[$field] = $dir;
                }
            }
        }

        return $sorts;
    }
}
