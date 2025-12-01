<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only admin and project managers can create/update projects
        return auth()->user()->isAdmin() || auth()->user()->isProjectManager();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $projectId = $this->route('project')?->id;

        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'client_id' => 'nullable|exists:users,id',
            'manager_id' => 'required|exists:users,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'nullable|date|after:start_date',
            'budget' => 'required|numeric|min:0|max:999999999999.99',
            'status' => 'required|in:planning,in_progress,on_hold,completed,cancelled',
            'priority' => 'required|in:low,medium,high,urgent',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Project name is required.',
            'name.max' => 'Project name cannot exceed 255 characters.',
            'manager_id.required' => 'Please select a project manager.',
            'manager_id.exists' => 'Selected manager does not exist.',
            'start_date.required' => 'Start date is required.',
            'start_date.after_or_equal' => 'Start date must be today or later.',
            'end_date.after' => 'End date must be after start date.',
            'budget.required' => 'Budget is required.',
            'budget.numeric' => 'Budget must be a valid number.',
            'budget.min' => 'Budget cannot be negative.',
            'status.required' => 'Please select a project status.',
            'status.in' => 'Invalid project status selected.',
            'priority.required' => 'Please select a priority level.',
            'priority.in' => 'Invalid priority level selected.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'manager_id' => 'project manager',
            'client_id' => 'client',
        ];
    }
}