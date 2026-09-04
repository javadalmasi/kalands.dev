<?php

namespace App\Services;

use App\Repositories\SettingsRepository;
use Illuminate\Support\Facades\Cache;

class VisitorIntelligenceService
{
    private SettingsRepository $settings;

    private const CACHE_KEY = 'visitor_intelligence:config';

    public function __construct(SettingsRepository $settings)
    {
        $this->settings = $settings;
    }

    public function getConfig(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            return $this->settings->get('visitor_intelligence.config', [
                'robots_pattern' => '(?:googlebot|googleother|bingbot|slurp|duckduckbot|baiduspider|yandexbot|facebookexternalhit|twitterbot|rogerbot|linkedinbot|embedly|quora link preview|showyoubot|outbrain|pinterest|developers\.google\.com\/\+\/web\/snippet|slackbot|vkshare|w3c_validator|redditbot|applebot|whatsapp|flipboard|tumblr|bitlybot|skypeuripreview|nuzzel|discordbot|google page speed|qwantify|pinterestbot|bitrix link preview|xing-contenttabreceiver|chrome-lighthouse|telegrambot|integration-test|headlesschrome|phantomjs|seoanalyzer|semrushbot|crawler|spider|bot|crawl|mediapartners|AhrefsSiteAudit|robot|gptbot|chatgpt|openai|openai-api|openai python|chatgpt-user|chatgpt-android|chatgpt-ios|claude-web|anthropic|claudebot|claude-crawler|perplexitybot|pplx|perplexity|grok|xai-client|xai-bot|meta-externalagent|llama|llamaindex|llamaindexbot|huggingface|hf-dataset|hf-inference|transformers-bot|youai|youbot|kagi|kagi-bot|kagibot|andi-search|andibot|neeva|neeva-ai|bravebot|brave-ai|qwantbot|mojeekbot|cohere|cohere-ai|cohere-bot|ai21|ai21labs|writerai|jasperai|copyai|deeplearningbot|deepai|dataforseo|datadome|datadomebot|diffbot|botify|botifyai|megaindex|megaindexbot|opensearchbot|semantic|semantic-scraper|semanticscholar|arxiv|arxivscraper|pubmedbot|pubmedai|archivebot|internetarchive|core-ai|core-academic|wget-ai|curl-ai|python-requests-ai|aiohttp-ai|selenium|playwright|puppeteer|headlesschrome|chromiumheadless|ml-bot|mlcrawler|ml-collector|llm|llmtraining|llmcrawler|llm-dataset|embeddingsbot|vectorizerbot|tokenizerbot|pineconebot|milvusbot|weaviatebot|vectarabot|autogpt|agentgpt|babyagi|reactagent|scrapybot|scraper|scraperai|smartcrawler|neural|neuralbot|neuralscraper|intellisearch|hypercrawler|hybridai|globalcrawlerai|universalai|smartindexbot)',
                'trusted_asns' => [15169, 8075, 136907, 714, 13238, 55967],
            ]);
        });
    }

    public function saveConfig(array $config): void
    {
        $this->settings->set('visitor_intelligence.config', $config);
        Cache::forget(self::CACHE_KEY);
    }
}
