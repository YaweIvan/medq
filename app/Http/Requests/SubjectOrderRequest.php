<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubjectOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user() && auth()->user()->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'subject_ids' => 'required|array|min:1',
            'subject_ids.*' => 'required|integer|exists:subjects,id',
        ];
    }

    public function messages(): array
    {
        return [
            'subject_ids.required' => 'Subject order data is required.',
            'subject_ids.array' => 'Invalid subject order format.',
            'subject_ids.*.exists' => 'One or more subjects do not exist.',
        ];
    }
}