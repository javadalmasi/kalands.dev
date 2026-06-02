<?php

namespace App\Jobs;

use App\Models\Category;
use App\Models\Product;
use App\Services\CategoryMappingService;
use App\Services\CategoryService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessProductCategoriesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected string $productId,
        protected array $breadcrumb,
        protected string $store
    ) {}

    public function handle(
        CategoryService $categoryService,
        CategoryMappingService $mappingService
    ): void {
        try {
            $product = Product::find($this->productId);
            if (!$product) return;

            // 1. Build/Find the category tree
            $category = $categoryService->findOrCreateFromBreadcrumb($this->breadcrumb, $this->store);

            // 2. Link product to the leaf category
            $product->update(['category_id' => $category->id]);

            // 3. Update counts
            $categoryService->updateProductCounts($category);

            // 4. Try to auto-map to Digikala if it's from another store
            if ($this->store !== 'digikala') {
                $mappingService->autoMap($category);
            }

        } catch (\Throwable $e) {
            Log::error("Error processing categories for product {$this->productId}: " . $e->getMessage());
        }
    }
}
