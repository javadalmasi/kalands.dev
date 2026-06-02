<?php

namespace App\Actions;

use App\Contracts\Action;
use App\Models\Comment;

class StoreCommentAction implements Action
{
    public function execute()
    {
        $payload = [
            'product_id' => request('product_id'),
            'parent_id' => request('parent_id'),
            'content' => strip_tags((string) request('content')),
            'status' => Comment::STATUS_PENDING,
        ];

        if (auth()->check()) {
            $payload['user_id'] = auth()->id();
        } else {
            $payload['name'] = strip_tags((string) request('name'));
            $payload['email'] = request('email');
            $payload['ip_address'] = request()->ip();
        }

        Comment::query()
            ->create($payload);

        return back()
            ->with('message', 'نظر شما باموفقیت ثبت شد و پس از تایید در سایت نمایش داده میشود.');
    }
}
