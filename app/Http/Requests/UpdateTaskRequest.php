<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use App\Enums\TaskPriority;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|string',
            'description' => 'nullable|string',
            'priority' => ['nullable', new Enum(TaskPriority::class)],
            'assignee_id' => 'nullable|exists:users,id',
        ];
    }
}
