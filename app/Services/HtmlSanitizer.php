<?php

namespace App\Services;

class HtmlSanitizer
{
    public function sanitize(string $html): string
    {
        $document = new \DOMDocument;
        libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="utf-8" ?><div>'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        foreach (iterator_to_array($document->getElementsByTagName('*')) as $element) {
            if (in_array(strtolower($element->nodeName), ['script', 'style', 'iframe', 'object', 'embed'], true)) {
                $element->parentNode?->removeChild($element);

                continue;
            }

            foreach (iterator_to_array($element->attributes ?? []) as $attribute) {
                $name = strtolower($attribute->nodeName);
                $value = strtolower(trim($attribute->nodeValue ?? ''));
                if (str_starts_with($name, 'on') || $name === 'style' || (in_array($name, ['href', 'src'], true) && str_starts_with($value, 'javascript:'))) {
                    $element->removeAttribute($attribute->nodeName);
                }
            }
        }

        $wrapper = $document->getElementsByTagName('div')->item(0);

        return $wrapper ? collect(iterator_to_array($wrapper->childNodes))->map(fn ($node): string => $document->saveHTML($node))->implode('') : '';
    }
}
