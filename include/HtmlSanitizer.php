<?php

namespace Genealogy\Include;

class HtmlSanitizer
{
    public function sanitizeBasicHtml(string $html): string
    {
        $document = new \DOMDocument('1.0', 'UTF-8');
        $previousErrorSetting = libxml_use_internal_errors(true);
        try {
            $loaded = $document->loadHTML(
                '<?xml encoding="UTF-8"><div id="tree-text-root">' . $html . '</div>',
                LIBXML_NONET
            );
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previousErrorSetting);
        }

        if (!$loaded) {
            throw new \RuntimeException('Unable to sanitize family tree text.');
        }

        $root = $document->getElementById('tree-text-root');
        if (!$root) {
            throw new \RuntimeException('Unable to find family tree text after parsing.');
        }

        $safeHtml = '';
        $htmlEscaper = new HtmlEscaper();
        foreach ($root->childNodes as $child) {
            $safeHtml .= $this->sanitizeNode($child, $htmlEscaper);
        }

        return $safeHtml;
    }

    private function sanitizeNode(\DOMNode $node, HtmlEscaper $htmlEscaper): string
    {
        if ($node->nodeType === XML_TEXT_NODE) {
            return $htmlEscaper->escHtml($node->nodeValue);
        }

        if (!$node instanceof \DOMElement) {
            return '';
        }

        $tag = strtolower($node->nodeName);
        if (in_array($tag, ['script', 'style', 'iframe', 'object', 'embed', 'svg', 'math', 'template'], true)) {
            return '';
        }

        $children = '';
        foreach ($node->childNodes as $child) {
            $children .= $this->sanitizeNode($child, $htmlEscaper);
        }

        if (!in_array($tag, ['a', 'b', 'strong', 'i', 'em', 'br', 'small'], true)) {
            return $children;
        }
        if ($tag === 'br') {
            return '<br>';
        }
        if ($tag !== 'a') {
            return '<' . $tag . '>' . $children . '</' . $tag . '>';
        }

        $href = trim($node->getAttribute('href'));
        if (
            $href === ''
            || preg_match('/[\x00-\x20\x7f\\\\]/', $href)
            || substr($href, 0, 2) === '//'
        ) {
            return $children;
        }

        $scheme = parse_url($href, PHP_URL_SCHEME);
        if ($scheme === false || ($scheme !== null && !in_array(strtolower($scheme), ['http', 'https', 'mailto'], true))) {
            return $children;
        }

        return '<a href="' . $htmlEscaper->escHtml($href) . '">' . $children . '</a>';
    }
}
