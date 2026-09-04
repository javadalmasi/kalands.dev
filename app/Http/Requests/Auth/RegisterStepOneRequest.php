<?php

namespace App\Http\Requests\Auth;

use App\Models\Admin;
use App\Models\User;
use App\Rules\ValidJsChallenge;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

class RegisterStepOneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $identifier = trim((string) enNumber((string) $this->input('identifier')));
        if (isPhoneOrEmail($identifier) === 'phone') {
            $identifier = normalizePhoneNumber($identifier);
        }
        $this->merge([
            'identifier' => $identifier,
        ]);
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['required', 'string', 'max:120'],
            'identifier' => [
                'required',
                'string',
                function (string $attribute, mixed $value, Closure $fail) {
                    $type = isPhoneOrEmail((string) $value);
                    if (! $type) {
                        $fail('شماره موبایل یا ایمیل معتبر وارد کنید.');

                        return;
                    }

                    if ($type === 'email') {
                        $existsInUsers = User::query()->where('email', $value)->exists();
                        $existsInAdmins = Admin::query()->where('email_address', $value)->exists();
                    } else {
                        $existsInUsers = User::query()->where('phone', $value)->exists();
                        $existsInAdmins = Admin::query()->where('mobile_number', $value)->exists();
                    }

                    if ($existsInUsers || $existsInAdmins) {
                        $fail('این شناسه قبلا ثبت شده است.');
                    }
                },
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[^a-zA-Z0-9]/',
            ],
            'js_challenge_key' => ['required', 'string'],
            'js_challenge_answer' => ['required', new ValidJsChallenge('auth_register_step1')],
        ];
    }
}
