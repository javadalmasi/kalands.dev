<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = auth()->id();

        return [
            'first_name' => ['required', 'max:120'],
            'last_name' => ['required', 'max:120'],
            'email' => ['nullable', 'required_without:phone', 'email', Rule::unique('users', 'email')->ignore($userId)],
            'phone' => ['nullable', 'required_without:email', 'regex:/^09[0-9]{9}$/', Rule::unique('users', 'phone')->ignore($userId)],
            'theme_preference' => ['nullable', 'in:light,dark'],
            'profile_bio' => ['nullable', 'max:500'],
        ];
    }
}
