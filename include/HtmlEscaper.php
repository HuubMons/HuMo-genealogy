<?php

/**
 * Function to safely show text on screen (in all HTML pages like forms etc.)
 */

namespace Genealogy\Include;

class HtmlEscaper
{
    // *** Default HTML escaping function ***
    public function escHtml($text): string
    {
        // *** First remove tags ***
        //$text = strip_tags($text, '<p><a><br>');

        return htmlspecialchars(
            $text ?? '',
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8'
        );
    }

    // *** HTML escaping function for decoded entities ***
    public function escHtmlDecoded($text): string
    {
        // Remark: users can replace this special character if needed.
        $text = str_replace('&#134;', '†', (string) $text);

        return $this->escHtml(
            html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8')
        );
    }
}
