<?php 
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Clients cannot create/update tasks
        return !auth()->user()->isClient();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'project_id' => 'required|exists:projects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:10000',
            'assigned_to' => 'nullable|exists:users,id',
            'priority' => 'required|in:low,medium,high,urgent',
            'status' => 'required|in:todo,in_progress,review,completed,cancelled',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:start_date',
            'estimated_hours' => 'nullable|integer|min:0|max:1000',
            'progress' => 'nullable|integer|min:0|max:100',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'project_id.required' => 'Please select a project.',
            'project_id.exists' => 'Selected project does not exist.',
            'title.required' => 'Task title is required.',
            'title.max' => 'Task title cannot exceed 255 characters.',
            'assigned_to.exists' => 'Selected user does not exist.',
            'priority.required' => 'Please select a priority level.',
            'priority.in' => 'Invalid priority level selected.',
            'status.required' => 'Please select a task status.',
            'status.in' => 'Invalid task status selected.',
            'due_date.after_or_equal' => 'Due date must be equal to or after start date.',
            'estimated_hours.integer' => 'Estimated hours must be a whole number.',
            'estimated_hours.min' => 'Estimated hours cannot be negative.',
            'estimated_hours.max' => 'Estimated hours cannot exceed 1000.',
            'progress.min' => 'Progress cannot be less than 0%.',
            'progress.max' => 'Progress cannot exceed 100%.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Auto-complete task if progress is 100%
        if ($this->progress == 100 && $this->status !== 'completed') {
            $this->merge([
                'status' => 'completed',
            ]);
        }
    }
}