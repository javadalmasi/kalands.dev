<?php

namespace App\Http\Requests\Auth;

use App\Rules\ValidJsChallenge;
use Illuminate\Foundation\Http\FormRequest;

class RegisterStepTwoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'theme_preference' => ['nullable', 'in:light,dark'],
            'profile_bio' => ['nullable', 'string', 'max:500'],
            'marketing_opt_in' => ['nullable', 'boolean'],
            'js_challenge_key' => ['required', 'string'],
            'js_challenge_answer' => ['required', new ValidJsChallenge('auth_register_step2')],
        ];
    }
}
