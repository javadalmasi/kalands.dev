<?php

namespace App\Actions;

use App\Contracts\Action;
use App\Models\Comment;
use App\Models\Product;
use App\Models\ProductIdMapping;

class StoreCommentAction implements Action
{
    private function resolveProductId(string $productId): string
    {
        $product = Product::find($productId);
        if (! $product) {
            return $productId;
        }

        $mapping = ProductIdMapping::where('old_product_id', $productId)
            ->where('store', $product->store)
            ->where('is_active', true)
            ->first();

        return $mapping?->new_product_id ?? $productId;
    }

    public function execute()
    {
        $originalProductId = request('product_id');
        $resolvedProductId = $this->resolveProductId($originalProductId);

        $payload = [
            'product_id' => $resolvedProductId,
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
