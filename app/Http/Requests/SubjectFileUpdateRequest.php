<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubjectFileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user() && auth()->user()->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'excel_file' => [
                'required',
                'file',
                'mimes:xlsx,xls,csv',
                'max:10240' // 10MB max
            ],
            'quiz_id' => 'required|integer|exists:quizzes,id',
            'time_per_question' => 'nullable|integer|min:10|max:600',
        ];
    }

    public function messages(): array
    {
        return [
            'excel_file.required' => 'Please select an Excel or CSV file.',
            'excel_file.mimes' => 'File must be Excel (.xlsx, .xls) or CSV format.',
            'excel_file.max' => 'File size must not exceed 10MB.',
            'quiz_id.required' => 'Quiz selection is required.',
            'quiz_id.exists' => 'Selected quiz does not exist.',
            'time_per_question.min' => 'Time per question must be at least 10 seconds.',
            'time_per_question.max' => 'Time per question cannot exceed 600 seconds.',
        ];
    }
}