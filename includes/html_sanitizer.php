<?php
// ==============================================================================
// TRUENORTH GROUP — HTML SANITIZER (allowlist)
// Used for rich-text blog content. Removes scripts, event handlers, javascript:
// URLs, unknown tags/attributes and dangerous CSS, while keeping normal article
// formatting (headings, lists, tables, links, images, inline styles).
// ==============================================================================

if (!function_exists('tn_sanitize_html')) {

    function tn_sanitize_html($html)
    {
        $html = (string)$html;
        if (trim($html) === '') {
            return '';
        }

        $allowedTags = [
            'p', 'br', 'hr', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'strong', 'b', 'em', 'i', 'u', 's', 'small',
            'sup', 'sub', 'mark', 'blockquote', 'q', 'cite', 'ul', 'ol', 'li', 'dl', 'dt', 'dd', 'a', 'img',
            'figure', 'figcaption', 'table', 'thead', 'tbody', 'tfoot', 'tr', 'th', 'td', 'caption', 'colgroup',
            'col', 'div', 'span', 'section', 'article', 'aside', 'pre', 'code', 'abbr', 'time', 'iframe',
        ];
        $allowedAttrs = [
            '*'      => ['class', 'style', 'id', 'title', 'lang', 'dir'],
            'a'      => ['href', 'target', 'rel'],
            'img'    => ['src', 'alt', 'width', 'height', 'loading', 'decoding', 'srcset', 'sizes'],
            'th'     => ['colspan', 'rowspan', 'scope'],
            'td'     => ['colspan', 'rowspan'],
            'col'    => ['span'],
            'ol'     => ['start', 'type', 'reversed'],
            'time'   => ['datetime'],
            'iframe' => ['src', 'width', 'height', 'allow', 'allowfullscreen', 'loading', 'frameborder'],
        ];
        // Tags whose whole content must disappear
        $dropWithContent = ['script', 'style', 'noscript', 'template', 'object', 'embed', 'applet', 'form', 'input',
            'button', 'select', 'textarea', 'svg', 'math', 'link', 'meta', 'base', 'frame', 'frameset'];

        if (!class_exists('DOMDocument')) {
            // Fallback without ext-dom: strip unknown tags and all risky attributes
            foreach ($dropWithContent as $t) {
                $html = preg_replace('#<' . $t . '\b[^>]*>.*?</' . $t . '\s*>#is', '', $html);
            }
            $html = strip_tags($html, '<' . implode('><', array_diff($allowedTags, ['iframe'])) . '>');
            $html = preg_replace('/\s+on[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html);
            $html = preg_replace('/(href|src)\s*=\s*("|\')\s*(javascript|vbscript|data(?!:image\/))\s*:[^"\']*\2/i', '$1="#"', $html);
            return $html;
        }

        $prev = libxml_use_internal_errors(true);
        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->loadHTML('<?xml encoding="UTF-8"><div id="tn-sanitize-root">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($prev);

        $root = $doc->getElementById('tn-sanitize-root');
        if (!$root) {
            return htmlspecialchars($html, ENT_QUOTES, 'UTF-8');
        }

        $walk = function (DOMNode $node) use (&$walk, $allowedTags, $allowedAttrs, $dropWithContent, $doc) {
            for ($child = $node->firstChild; $child !== null; $child = $next) {
                $next = $child->nextSibling;

                if ($child instanceof DOMComment || $child instanceof DOMProcessingInstruction || $child instanceof DOMCdataSection) {
                    $node->removeChild($child);
                    continue;
                }
                if (!($child instanceof DOMElement)) {
                    continue;
                }

                $tag = strtolower($child->tagName);
                if (in_array($tag, $dropWithContent, true)) {
                    $node->removeChild($child);
                    continue;
                }
                if (!in_array($tag, $allowedTags, true)) {
                    // Unknown tag: keep its (sanitised) children, drop the element itself
                    $walk($child);
                    while ($child->firstChild) {
                        $node->insertBefore($child->firstChild, $child);
                    }
                    $node->removeChild($child);
                    continue;
                }

                $okAttrs = array_merge($allowedAttrs['*'], $allowedAttrs[$tag] ?? []);
                for ($i = $child->attributes->length - 1; $i >= 0; $i--) {
                    $attr = $child->attributes->item($i);
                    $name = strtolower($attr->name);
                    $value = trim($attr->value);
                    if (!in_array($name, $okAttrs, true)) {
                        $child->removeAttribute($attr->name);
                        continue;
                    }
                    if (in_array($name, ['href', 'src', 'srcset'], true) && !tn_sanitize_url_ok($value, $name, $tag)) {
                        $child->removeAttribute($attr->name);
                        continue;
                    }
                    if ($name === 'style') {
                        $clean = tn_sanitize_css($value);
                        if ($clean === '') {
                            $child->removeAttribute($attr->name);
                        } else {
                            $child->setAttribute('style', $clean);
                        }
                    }
                }

                if ($tag === 'iframe' && !preg_match('#^https://(www\.)?(youtube\.com|youtube-nocookie\.com|player\.vimeo\.com|www\.google\.com/maps)/#i', $child->getAttribute('src'))) {
                    $node->removeChild($child);
                    continue;
                }
                if ($tag === 'a' && strtolower($child->getAttribute('target')) === '_blank') {
                    $child->setAttribute('rel', 'noopener noreferrer');
                }

                $walk($child);
            }
        };
        $walk($root);

        $out = '';
        foreach ($root->childNodes as $c) {
            $out .= $doc->saveHTML($c);
        }
        return $out;
    }

    function tn_sanitize_url_ok($url, $attr, $tag)
    {
        $u = preg_replace('/[\x00-\x20]+/', '', html_entity_decode($url, ENT_QUOTES, 'UTF-8'));
        if ($u === '' || $u[0] === '#' || $u[0] === '/' || $u[0] === '?') {
            return strpos($u, '//') !== 0 || preg_match('#^//[a-z0-9.-]+/#i', $u);
        }
        if (preg_match('#^(https?:|mailto:|tel:)#i', $u)) {
            return true;
        }
        if ($tag === 'img' && $attr === 'src' && preg_match('#^data:image/(png|jpe?g|gif|webp);base64,#i', $u)) {
            return true;
        }
        // relative paths like "images/x.webp" (no scheme)
        return !preg_match('#^[a-z][a-z0-9+.-]*:#i', $u);
    }

    function tn_sanitize_css($css)
    {
        $decoded = html_entity_decode($css, ENT_QUOTES, 'UTF-8');
        $decoded = preg_replace('#/\*.*?\*/#s', '', $decoded);
        if (preg_match('/expression\s*\(|javascript:|vbscript:|@import|behavior\s*:|-moz-binding|\\\\/i', $decoded)) {
            return '';
        }
        $keep = [];
        foreach (explode(';', $decoded) as $decl) {
            if (strpos($decl, ':') === false) continue;
            [$prop, $val] = array_map('trim', explode(':', $decl, 2));
            if ($prop === '' || $val === '' || !preg_match('/^-?[a-z-]+$/i', $prop)) continue;
            if (preg_match('/^position$/i', $prop) && preg_match('/fixed|sticky/i', $val)) continue; // no page-overlay tricks
            if (preg_match('/url\s*\(/i', $val) && !preg_match('#url\(\s*[\'"]?(https?://|/)[^)\'"]*[\'"]?\s*\)#i', $val)) continue;
            $keep[] = strtolower($prop) . ': ' . $val;
        }
        return implode('; ', $keep);
    }
}
