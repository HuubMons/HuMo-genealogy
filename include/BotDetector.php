<?php

namespace Genealogy\Include;

class BotDetector
{
    public function isBot(): bool
    {
        // Old code:
        // $index['bot_visit'] = preg_match('/bot|spider|crawler|curl|Yahoo|Google|^$/i', $_SERVER['HTTP_USER_AGENT']);

        // *** For testing purposes, simulate a bot user agent ***
        // $_SERVER['HTTP_USER_AGENT'] = 'bot';

        return preg_match('/bot|spider|crawler|curl|Yahoo|Google|Bingbot|DuckDuckBot|Baiduspider|Yandex|Sogou|facebookexternalhit|Slackbot|Discordbot|Applebot|SemrushBot|AhrefsBot|MJ12bot|^$/i', $_SERVER['HTTP_USER_AGENT']) === 1;
    }
}
