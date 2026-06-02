<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Str;

class CategoryService
{
    public function __construct(protected CategoryVectorService $vectorService) {}

    /**
     * Process a breadcrumb array and return the leaf category.
     *
     * @param array $breadcrumb Array of items with 'title' and optional 'id'
     * @param string $store 'digikala' or 'basalam'
     * @return Category
     */
    public function findOrCreateFromBreadcrumb(array $breadcrumb, string $store): Category
    {
        $parentId = null;
        $leafCategory = null;

        // Skip root label if it's just the store name (often the first breadcrumb in Digikala)
        $skipTitles = ['دیجی‌کالا', 'دیجیکالا', 'باسلام'];

        foreach ($breadcrumb as $index => $item) {
            $title = $item['title'] ?? $item['header'] ?? null;
            if (!$title) continue;

            // Normalize title
            $title = trim($title);

            // Skip store name if it's at the start
            if ($index === 0 && in_array($title, $skipTitles)) {
                continue;
            }

            $externalId = $item['id'] ?? null;

            $category = Category::query()->updateOrCreate(
                [
                    'store' => $store,
                    'title' => $title,
                    'parent_id' => $parentId,
                ],
                [
                    'external_id' => $externalId,
                ]
            );

            // Update vector if not set OR if vector source/model has changed to something more specific
            if (!$category->vector) {
                $category->vector = $this->vectorService->getVector($title);
                $category->vector_source = $this->vectorService->getSource();
                $category->vector_model = $this->vectorService->getModel();
                $category->save();
            }

            $parentId = $category->id;
            $leafCategory = $category;
        }

        return $leafCategory;
    }

    /**
     * Update product counts for a category and its ancestors.
     */
    public function updateProductCounts(Category $category): void
    {
        $current = $category;
        while ($current) {
            $count = Product::where('category_id', $current->id)->count();

            // Also include counts from children if we want aggregate count
            $childrenIds = Category::where('parent_id', $current->id)->pluck('id');
            if ($childrenIds->isNotEmpty()) {
                $count += Product::whereIn('category_id', $childrenIds)->count();
            }

            $current->update(['product_count' => $count]);
            $current = $current->parent;
        }
    }

    /**
     * Get the full category tree.
     */
    public function getTree(string $store = 'digikala')
    {
        return Category::where('store', $store)
            ->whereNull('parent_id')
            ->with($this->getRecursiveRelationships())
            ->get();
    }

    /**
     * Import SnappShop category tree.
     */
    public function importSnappShop(array $menus): void
    {
        foreach ($menus as $menuGroup) {
            foreach ($menuGroup as $root) {
                $this->processSnappNode($root);
            }
        }
    }

    protected function processSnappNode(array $node, ?int $parentId = null): void
    {
        $title = trim($node['title'] ?? '');
        $href = $node['href'] ?? '';

        // Skip if contains query string
        if (str_contains($href, '?')) {
            if (!empty($node['children'])) {
                foreach ($node['children'] as $child) {
                    $this->processSnappNode($child, $parentId);
                }
            }
            return;
        }

        // Normalize URL to path only
        $path = $href;
        if (str_starts_with($href, 'http')) {
            $path = parse_url($href, PHP_URL_PATH);
        }

        // Filter: Must start with /category/ and should not have sub-segments (per user request)
        // Correct format: /category/mobile
        // Incorrect format: /category/mobile/apple
        $segments = array_values(array_filter(explode('/', trim($path, '/'))));
        $isValidUrl = (count($segments) === 2 && $segments[0] === 'category');

        if (!$title || $title === 'همه دسته‌بندی‌ها' || $title === 'همه کالاها' || !$isValidUrl) {
            if (!empty($node['children'])) {
                foreach ($node['children'] as $child) {
                    $this->processSnappNode($child, $parentId);
                }
            }
            return;
        }

        // Refine Title: Remove "براساس..." or "بر اساس..."
        $refinedTitle = preg_replace('/\s*ب[ر]?\s*اساس.*/u', '', $title);
        $nameEn = $segments[1] ?? null;

        $category = Category::query()->updateOrCreate(
            [
                'store' => 'snappshop',
                'title' => $refinedTitle,
                'parent_id' => $parentId,
            ],
            [
                'name_en' => $nameEn,
                'external_id' => $node['id'] ?? null,
            ]
        );

        if (!$category->vector) {
            $category->vector = $this->vectorService->getVector($title);
            $category->vector_source = $this->vectorService->getSource();
            $category->vector_model = $this->vectorService->getModel();
            $category->save();
        }

        if (!empty($node['children'])) {
            foreach ($node['children'] as $child) {
                $this->processSnappNode($child, $category->id);
            }
        }

        // Auto-map to Digikala immediately after creation/update
        app(CategoryMappingService::class)->autoMap($category);
    }

    protected function getRecursiveRelationships($depth = 10): array
    {
        if ($depth <= 0) return [];

        return ['children' => function($query) use ($depth) {
            $query->with($this->getRecursiveRelationships($depth - 1));
        }];
    }

    /**
     * Cleanup redundant root categories like 'دیجی‌کالا'
     */
    public function cleanupRoots(): void
    {
        $redundant = Category::whereIn('title', ['دیجی‌کالا', 'دیجیکالا', 'باسلام'])->whereNull('parent_id')->get();
        foreach ($redundant as $root) {
            // Move children to top level
            Category::where('parent_id', $root->id)->update(['parent_id' => null]);
            $root->delete();
        }

        // Cleanup categories that are likely product titles (leaf nodes with high title length or only 1 product)
        // This is a heuristic: leaf categories with 0 or 1 product that don't match typical category patterns.
        $likelyProducts = Category::whereDoesntHave('children')
            ->where('product_count', '<=', 1)
            ->where('store', 'digikala')
            ->get();

        foreach ($likelyProducts as $cat) {
            // If the parent also has the same product, this leaf is redundant (likely a title)
            if ($cat->parent_id) {
                Product::where('category_id', $cat->id)->update(['category_id' => $cat->parent_id]);
                $cat->delete();
            }
        }
    }
}
