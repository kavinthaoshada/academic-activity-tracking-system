<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InviteStaffRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()->isAdmin(); }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255'],
            'email'       => [
                'required', 'email',
                Rule::unique('users', 'email'),
                Rule::unique('staff_invitations', 'email')->where(fn ($q) => $q->whereNull('accepted_at')),
            ],
            'employee_id' => ['nullable', 'string', 'max:50'],
            'department'  => ['nullable', 'string', 'max:100'],
            'phone'       => ['nullable', 'string', 'max:20'],
            'role'        => ['nullable', Rule::in(['admin', 'staff'])],
        ];
    }
}