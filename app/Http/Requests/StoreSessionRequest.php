<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSessionRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'academic_week_id'           => ['required', 'exists:academic_weeks,id'],
            'sessions'                   => ['required', 'array'],
            'sessions.*.planned'         => ['required', 'integer', 'min:0'],
            'sessions.*.actual'          => ['required', 'integer', 'min:0'],
            'sessions.*.remarks'         => ['nullable', 'string', 'max:500'],
        ];
    }
}