<?php 
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TimeLogRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Task access is checked by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'hours' => 'required|numeric|min:0.1|max:24',
            'log_date' => 'required|date|before_or_equal:today',
            'description' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'hours.required' => 'Please enter the number of hours worked.',
            'hours.numeric' => 'Hours must be a valid number.',
            'hours.min' => 'Hours must be at least 0.1 (6 minutes).',
            'hours.max' => 'Hours cannot exceed 24 hours per day.',
            'log_date.required' => 'Log date is required.',
            'log_date.before_or_equal' => 'Log date cannot be in the future.',
            'description.max' => 'Description cannot exceed 500 characters.',
        ];
    }
}