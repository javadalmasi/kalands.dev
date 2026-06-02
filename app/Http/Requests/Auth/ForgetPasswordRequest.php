<?php

namespace App\Http\Requests\Auth;

use App\Rules\ValidJsChallenge;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

class ForgetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $identifier = trim((string) enNumber((string) $this->input('identifier')));
        $this->merge([
            'identifier' => $identifier,
        ]);
    }

    public function rules(): array
    {
        return [
            'identifier' => [
                'required',
                'string',
                function (string $attribute, mixed $value, Closure $fail) {
                    if (!isPhoneOrEmail((string) $value)) {
                        $fail('شماره موبایل یا ایمیل معتبر وارد کنید.');
                    }
                },
            ],
            'js_challenge_key' => ['required', 'string'],
            'js_challenge_answer' => ['required', new ValidJsChallenge('auth_forget')],
        ];
    }
}
