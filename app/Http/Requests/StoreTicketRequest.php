<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'exists:ticket_categories,id'],
            'subject' => ['required', 'max:200'],
            'priority' => ['nullable', 'in:low,medium,high'],
            'message' => ['required'],
        ];
    }
}
