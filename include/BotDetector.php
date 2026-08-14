<?php

namespace Genealogy\Include;

class BotDetector
{
    public function isBot(): bool
    {
        // *** For testing purposes, simulate a bot user agent ***
        //$_SERVER['HTTP_USER_AGENT'] = 'bot';

        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        // *** Aug. 2026: old bots ***
        //return preg_match('/bot|spider|crawler|curl|Yahoo|Google|Bingbot|DuckDuckBot|Baiduspider|Yandex|Sogou|facebookexternalhit|Slackbot|Discordbot|Applebot|SemrushBot|AhrefsBot|MJ12bot|^$/i', $userAgent) === 1;

        // *** Aug. 2026: added more bots ***
        $patterns = [
            'bot',
            'spider',
            'crawler',
            'curl',
            'wget',
            'python',
            'Yahoo',
            'Google',
            'Bingbot',
            'DuckDuckBot',
            'Baiduspider',
            'Yandex',
            'Sogou',
            'facebookexternalhit',
            'Slackbot',
            'Discordbot',
            'Applebot',
            'SemrushBot',
            'AhrefsBot',
            'MJ12bot',
            'GPTBot',
            'CCBot',
            'PerplexityBot',
            'Claude-Web',
            'MstAgent',
            'DataForSeoBot',
            'anthropic',
            'openai',
            'scraper',
            'harvester',
            'downloader',
            'nikto',
            'nmap',
            'sqlmap',
            'masscan',
            'massccanner',
            'metabot',
            '^$'
        ];

        $pattern = '/' . implode('|', $patterns) . '/i';
        return preg_match($pattern, $userAgent) === 1;
    }
}
