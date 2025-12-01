<?php 
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TeamMemberRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->isAdmin() || auth()->user()->isProjectManager();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $projectId = $this->route('project')->id;

        return [
            'user_id' => [
                'required',
                'exists:users,id',
                // Prevent duplicate team member
                Rule::unique('team_members')->where(function ($query) use ($projectId) {
                    return $query->where('project_id', $projectId);
                }),
            ],
            'role' => 'required|in:lead,developer,designer,tester,analyst',
            'allocation_percentage' => 'required|integer|min:1|max:100',
            'joined_at' => 'required|date',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'user_id.required' => 'Please select a team member.',
            'user_id.exists' => 'Selected user does not exist.',
            'user_id.unique' => 'This user is already a member of this project.',
            'role.required' => 'Please select a role for the team member.',
            'role.in' => 'Invalid role selected.',
            'allocation_percentage.required' => 'Allocation percentage is required.',
            'allocation_percentage.min' => 'Allocation percentage must be at least 1%.',
            'allocation_percentage.max' => 'Allocation percentage cannot exceed 100%.',
            'joined_at.required' => 'Join date is required.',
            'joined_at.date' => 'Join date must be a valid date.',
        ];
    }
}