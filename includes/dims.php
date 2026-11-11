<?php
// ==============================================================================
// TRUENORTH GROUP — IMAGE DIMENSION HELPERS (Core Web Vitals / CLS)
// Reads config/image_dims.json (built by scripts/build-image-dims.py) and
// returns width/height attributes so every <img> reserves layout space.
// Safe no-op (returns '') when the image is unknown, so it never breaks
// existing templates.
// ==============================================================================

require_once dirname(__DIR__) . '/config/config.php';

function tn_img_dims($src) {
    static $map = null;
    if ($map === null) {
        $root = defined('ROOT_PATH') ? ROOT_PATH : dirname(__DIR__);
        $file = $root . '/config/image_dims.json';
        $map = is_file($file) ? (json_decode((string)@file_get_contents($file), true) ?: []) : [];
    }
    if (!is_array($map) || empty($map)) {
        return '';
    }
    $src = (string)$src;
    // Strip query string, then normalise to a site-relative path
    $src = preg_replace('/\?.*$/', '', $src);
    if (strpos($src, 'http') === 0) {
        $path = parse_url($src, PHP_URL_PATH) ?: '/';
    } else {
        $path = $src;
    }
    // Tolerate /public/ prefix and case-insensitive folder names
    $candidates = [
        $path,
        ltrim($path, '/'),
        strpos($path, '/public/') === 0 ? substr($path, 7) : $path,
    ];
    foreach ($candidates as $c) {
        if (isset($map[$c])) {
            return ' width="' . (int)$map[$c]['w'] . '" height="' . (int)$map[$c]['h'] . '"';
        }
        // case-insensitive fallback
        foreach ($map as $k => $d) {
            if (strcasecmp($k, $c) === 0) {
                return ' width="' . (int)$d['w'] . '" height="' . (int)$d['h'] . '"';
            }
        }
    }
    return '';
}

/**
 * Emit a complete, CLS-safe <img> tag.
 *   tn_img('/path.webp', 'Alt text', ['lazy' => true])
 */
function tn_img($src, $alt = '', $opts = []) {
    $dims = tn_img_dims($src);
    $lazy = !empty($opts['lazy']) ? ' loading="lazy"' : '';
    $dec  = empty($opts['eager']) ? ' decoding="async"' : '';
    $style = isset($opts['style']) ? ' style="' . $opts['style'] . '"' : '';
    $onerror = !empty($opts['onerror']) ? ' onerror="' . $opts['onerror'] . '"' : '';
    $cls = !empty($opts['class']) ? ' class="' . htmlspecialchars($opts['class'], ENT_QUOTES) . '"' : '';
    $id = !empty($opts['id']) ? ' id="' . htmlspecialchars($opts['id'], ENT_QUOTES) . '"' : '';
    $w = isset($opts['w']) ? ' width="' . (int)$opts['w'] . '"' : '';
    $h = isset($opts['h']) ? ' height="' . (int)$opts['h'] . '"' : '';
    // Prefer explicit w/h from caller, else the map lookup
    $dimsAttr = ($w || $h) ? trim($w . ' ' . $h) : $dims;
    return '<img src="' . htmlspecialchars($src, ENT_QUOTES) . '" alt="' . htmlspecialchars($alt, ENT_QUOTES) . '"'
        . $cls . $id . $dimsAttr . $lazy . $dec . $style . $onerror . ' />';
}
