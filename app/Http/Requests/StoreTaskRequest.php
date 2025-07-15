<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use App\Enums\TaskPriority;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('parent_id') && $this->input('parent_id') === 0) {
            $this->merge(['parent_id' => null]);
        }
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string',
            'description' => 'nullable|string',
            'priority' => ['required', new Enum(TaskPriority::class)],
            'parent_id' => 'nullable|exists:tasks,id',
            'assignee_id' => 'nullable|exists:users,id',
        ];
    }
}
