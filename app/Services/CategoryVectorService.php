<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Repositories\SettingsRepository;
use App\Models\AiUsageLog;

class CategoryVectorService
{
    protected array $settings;

    public function __construct(SettingsRepository $settingsRepository)
    {
        $this->settings = $settingsRepository->get('categories.settings', [
            'vector_engine' => 'local',
            'external_model' => 'gemma-4',
            'api_endpoint' => '',
            'api_key' => '',
        ]);
    }

    public function getVector(string $text): array
    {
        if ($this->settings['vector_engine'] === 'external' && !empty($this->settings['api_endpoint'])) {
            return $this->getExternalVector($text);
        }

        return $this->getLocalVector($text);
    }

    public function getSource(): string
    {
        return $this->settings['vector_engine'];
    }

    public function getModel(): string
    {
        return $this->settings['vector_engine'] === 'external'
            ? $this->settings['external_model']
            : 'ngram-3';
    }

    protected function getLocalVector(string $text, int $n = 3): array
    {
        $text = $this->normalizeText($text);
        $ngrams = [];
        $len = mb_strlen($text);

        for ($i = 0; $i <= $len - $n; $i++) {
            $gram = mb_substr($text, $i, $n);
            $ngrams[$gram] = ($ngrams[$gram] ?? 0) + 1;
        }

        return $ngrams;
    }

    protected function getExternalVector(string $text): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->settings['api_key'],
            ])->timeout(5)->post($this->settings['api_endpoint'], [
                'model' => $this->settings['external_model'],
                'input' => $text,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $vector = $data['embedding'] ?? $data['data'][0]['embedding'] ?? [];

                // Log usage
                AiUsageLog::create([
                    'model' => $this->settings['external_model'],
                    'action' => 'embedding',
                    'tokens_used' => $data['usage']['total_tokens'] ?? 0,
                    'request_count' => 1,
                    'metadata' => ['text_length' => mb_strlen($text)]
                ]);

                return $vector;
            }
        } catch (\Throwable $e) {}

        return $this->getLocalVector($text);
    }

    public function calculateSimilarity(array $vec1, array $vec2): float
    {
        if (empty($vec1) || empty($vec2)) return 0;

        $isNumeric = isset($vec1[0]) && is_numeric($vec1[0]);

        if ($isNumeric) {
            return $this->cosineSimilarityNumeric($vec1, $vec2);
        }

        return $this->cosineSimilarityAssociative($vec1, $vec2);
    }

    protected function cosineSimilarityNumeric(array $vec1, array $vec2): float
    {
        $dotProduct = 0;
        $normA = 0;
        $normB = 0;
        $count = min(count($vec1), count($vec2));

        for ($i = 0; $i < $count; $i++) {
            $dotProduct += $vec1[$i] * $vec2[$i];
            $normA += $vec1[$i] * $vec1[$i];
            $normB += $vec2[$i] * $vec2[$i];
        }

        return ($normA == 0 || $normB == 0) ? 0 : $dotProduct / (sqrt($normA) * sqrt($normB));
    }

    protected function cosineSimilarityAssociative(array $vec1, array $vec2): float
    {
        $dotProduct = 0;
        $normA = 0;
        $normB = 0;

        $allKeys = array_unique(array_merge(array_keys($vec1), array_keys($vec2)));

        foreach ($allKeys as $key) {
            $val1 = $vec1[$key] ?? 0;
            $val2 = $vec2[$key] ?? 0;

            $dotProduct += $val1 * $val2;
            $normA += $val1 * $val1;
            $normB += $val2 * $val2;
        }

        return ($normA == 0 || $normB == 0) ? 0 : $dotProduct / (sqrt($normA) * sqrt($normB));
    }

    public function findSimilar(\App\Models\Category $category, int $limit = 5): array
    {
        $targetStores = ['digikala', 'basalam', 'snappshop'];
        $targets = \App\Models\Category::where('id', '!=', $category->id)->get();
        $matches = [];

        foreach ($targets as $target) {
            $score = $this->calculateSimilarity($category->vector ?? [], $target->vector ?? []);
            if ($score > 0.1) {
                $matches[] = [
                    'id' => $target->id,
                    'title' => $target->title,
                    'store' => $target->store,
                    'score' => round($score * 100, 1),
                ];
            }
        }

        usort($matches, fn($a, $b) => $b['score'] <=> $a['score']);

        return array_slice($matches, 0, $limit);
    }

    public function testEmbedding(string $text): array
    {
        $start = microtime(true);
        $vector = $this->getVector($text);
        $duration = round((microtime(true) - $start) * 1000, 2);

        return [
            'ok' => !empty($vector),
            'source' => $this->getSource(),
            'model' => $this->getModel(),
            'duration_ms' => $duration,
            'vector_preview' => array_slice($vector, 0, 5),
            'vector_size' => count($vector),
        ];
    }

    private function normalizeText(string $text): string
    {
        $text = mb_strtolower($text);
        $text = preg_replace('/[^\p{L}\p{N}]+/u', ' ', $text);
        return trim($text);
    }
}
