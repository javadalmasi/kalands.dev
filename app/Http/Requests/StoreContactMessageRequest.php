<?php

namespace App\Http\Requests;

use App\Rules\ValidJsChallenge;
use Illuminate\Foundation\Http\FormRequest;

class StoreContactMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'max:120'],
            'email' => ['required', 'email', 'max:180'],
            'subject' => ['required', 'max:200'],
            'message' => ['required'],
            'js_challenge_key' => ['required', 'string'],
            'js_challenge_answer' => ['required', new ValidJsChallenge('contact_form')],
        ];
    }
}
