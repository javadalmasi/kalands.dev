<?php

namespace App\Services;

use App\Models\Category;
use App\Models\CategoryMapping;

class CategoryMappingService
{
    public function __construct(
        protected CategoryVectorService $vectorService,
        protected CategoryService $categoryService
    ) {}

    /**
     * Try to find and map a source category (Basalam/SnappShop) to a Digikala equivalent.
     */
    public function autoMap(Category $sourceCategory, float $threshold = 0.6): ?CategoryMapping
    {
        if ($sourceCategory->store === 'digikala') return null;

        $digikalaCategories = Category::where('store', 'digikala')->get();
        $bestMatch = null;
        $highestScore = 0;

        foreach ($digikalaCategories as $dkCategory) {
            $score = $this->vectorService->calculateSimilarity(
                $sourceCategory->vector ?? [],
                $dkCategory->vector ?? []
            );

            if ($score > $highestScore) {
                $highestScore = $score;
                $bestMatch = $dkCategory;
            }
        }

        if ($bestMatch && $highestScore >= $threshold) {
            return CategoryMapping::updateOrCreate(
                [
                    'source_category_id' => $sourceCategory->id,
                ],
                [
                    'digikala_category_id' => $bestMatch->id,
                    'confidence' => $highestScore,
                    'is_manual' => false,
                ]
            );
        }

        return null;
    }

    /**
     * Perform bulk auto-mapping for all non-Digikala categories.
     */
    public function syncAll(): int
    {
        $count = 0;
        $sources = Category::whereIn('store', ['basalam', 'snappshop'])->get();

        foreach ($sources as $source) {
            $mapping = $this->autoMap($source);
            if ($mapping) $count++;
        }

        return $count;
    }
}
