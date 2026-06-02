/**
 * Utility for handling visitor state on the client side.
 */
export const VisitorHelper = {
    /**
     * Comprehensive regex for detecting common crawlers.
     */
    getVPattern() {
        const pattern = window.visitorPattern || '(?:googlebot|googleother|bingbot|slurp|duckduckbot|baiduspider|yandexbot|facebookexternalhit|twitterbot|rogerbot|linkedinbot|embedly|quora link preview|showyoubot|outbrain|pinterest|developers\\.google\\.com\\/\\+\\/web\\/snippet|slackbot|vkshare|w3c_validator|redditbot|applebot|whatsapp|flipboard|tumblr|bitlybot|skypeuripreview|nuzzel|discordbot|google page speed|qwantify|pinterestbot|bitrix link preview|xing-contenttabreceiver|chrome-lighthouse|telegrambot|integration-test|headlesschrome|phantomjs|seoanalyzer|semrushbot|crawler|spider|bot|crawl|mediapartners|AhrefsSiteAudit|robot|gptbot|chatgpt|openai|openai-api|openai python|chatgpt-user|chatgpt-android|chatgpt-ios|claude-web|anthropic|claudebot|claude-crawler|perplexitybot|pplx|perplexity|grok|xai-client|xai-bot|meta-externalagent|llama|llamaindex|llamaindexbot|huggingface|hf-dataset|hf-inference|transformers-bot|youai|youbot|kagi|kagi-bot|kagibot|andi-search|andibot|neeva|neeva-ai|bravebot|brave-ai|qwantbot|mojeekbot|cohere|cohere-ai|cohere-bot|ai21|ai21labs|writerai|jasperai|copyai|deeplearningbot|deepai|dataforseo|datadome|datadomebot|diffbot|botify|botifyai|megaindex|megaindexbot|opensearchbot|semantic|semantic-scraper|semanticscholar|arxiv|arxivscraper|pubmedbot|pubmedai|archivebot|internetarchive|core-ai|core-academic|wget-ai|curl-ai|python-requests-ai|aiohttp-ai|selenium|playwright|puppeteer|headlesschrome|chromiumheadless|ml-bot|mlcrawler|ml-collector|llm|llmtraining|llmcrawler|llm-dataset|embeddingsbot|vectorizerbot|tokenizerbot|pineconebot|milvusbot|weaviatebot|vectarabot|autogpt|agentgpt|babyagi|reactagent|scrapybot|scraper|scraperai|smartcrawler|neural|neuralbot|neuralscraper|intellisearch|hypercrawler|hybridai|globalcrawlerai|universalai|smartindexbot)';
        return new RegExp(pattern, 'i');
    },

    /**
     * Checks if the current visitor is likely a bot.
     * @returns {Promise<boolean>}
     */
    async isBot() {
        // Priority 1: User-Agent check (Synchronous/Instant)
        const userAgent = navigator.userAgent;
        if (userAgent && this.getVPattern().test(userAgent)) {
            return true;
        }

        // Priority 2: ASN/Country check via API (Asynchronous)
        // We cache the result in session storage to avoid multiple calls per session
        const cached = sessionStorage.getItem('v_st');
        if (cached !== null) {
            return cached === 'on';
        }

        try {
            const response = await fetch('/api/info');
            const text = await response.text();
            const lines = text.split('\n');
            const data = {};
            lines.forEach(line => {
                const [key, value] = line.split('=');
                if (key) data[key] = value;
            });

            const isBot = data.st === 'on' || data.ast === 'on';
            sessionStorage.setItem('v_st', isBot ? 'on' : 'off');
            return isBot;
        } catch (e) {
            console.error('Failed to fetch visitor info', e);
            return false; // Assume human on error to not degrade experience
        }
    }
};
