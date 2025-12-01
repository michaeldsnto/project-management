<?php 
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CommentRequest extends FormRequest
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
            'content' => 'required|string|min:1|max:2000',
            'parent_id' => 'nullable|exists:comments,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'content.required' => 'Comment cannot be empty.',
            'content.min' => 'Comment must be at least 1 character.',
            'content.max' => 'Comment cannot exceed 2000 characters.',
            'parent_id.exists' => 'Parent comment does not exist.',
        ];
    }
}