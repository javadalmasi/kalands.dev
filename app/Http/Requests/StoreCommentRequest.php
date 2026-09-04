<?php

namespace App\Http\Requests;

use App\Models\Comment;
use App\Rules\ValidJsChallenge;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'parent_id' => [
                'nullable',
                'exists:comments,id',
                function (string $attribute, mixed $value, Closure $fail) {
                    if (! $value) {
                        return;
                    }

                    $parent = Comment::query()->find($value);
                    if ($parent && $parent->parent_id) {
                        $fail('پاسخ تو در تو فقط تا یک سطح مجاز است.');
                    }
                },
            ],
            'product_id' => ['required', 'exists:products,id'],
            'name' => [auth()->check() ? 'nullable' : 'required', 'max:100'],
            'email' => [auth()->check() ? 'nullable' : 'required', 'email', 'max:150'],
            'content' => ['required', 'string'],
            'js_challenge_key' => ['required', 'string'],
            'js_challenge_answer' => ['required', new ValidJsChallenge('comment_form')],
        ];
    }
}
