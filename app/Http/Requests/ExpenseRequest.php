<?php namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExpenseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Clients cannot create expenses
        return !auth()->user()->isClient();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'amount' => 'required|numeric|min:0|max:999999999.99',
            'category' => 'required|in:salary,equipment,software,travel,miscellaneous',
            'expense_date' => 'required|date|before_or_equal:today',
            'receipt' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Expense title is required.',
            'title.max' => 'Expense title cannot exceed 255 characters.',
            'amount.required' => 'Expense amount is required.',
            'amount.numeric' => 'Amount must be a valid number.',
            'amount.min' => 'Amount cannot be negative.',
            'amount.max' => 'Amount is too large.',
            'category.required' => 'Please select an expense category.',
            'category.in' => 'Invalid expense category selected.',
            'expense_date.required' => 'Expense date is required.',
            'expense_date.before_or_equal' => 'Expense date cannot be in the future.',
            'receipt.file' => 'Receipt must be a valid file.',
            'receipt.mimes' => 'Receipt must be a JPG, PNG, or PDF file.',
            'receipt.max' => 'Receipt file size cannot exceed 2MB.',
        ];
    }
}