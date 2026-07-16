<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitAnswerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isQuizzer();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'question_id' => 'required|exists:questions,id',
            'selected_answer' => 'nullable|in:A,B,C,D,E',
            'time_taken' => 'nullable|integer|min:0|max:3600',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'question_id.required' => 'Question ID is required.',
            'question_id.exists' => 'Invalid question.',
            'selected_answer.in' => 'Invalid answer option.',
            'time_taken.integer' => 'Time taken must be a number.',
            'time_taken.max' => 'Time taken cannot exceed 1 hour.',
        ];
    }
}
