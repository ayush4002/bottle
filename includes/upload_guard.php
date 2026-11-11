<?php
// ==============================================================================
// TRUENORTH GROUP — UPLOAD GUARD
// Central validation for every admin image upload. Rejects anything that is
// not a real image (by extension AND content), and rejects SVGs containing
// scripts, event handlers or external references (stored-XSS vectors).
// ==============================================================================

if (!function_exists('tn_upload_safe_image_ext')) {

    /**
     * Validate an uploaded image.
     *
     * @param string $tmpPath  $_FILES[...]['tmp_name']
     * @param string $origName $_FILES[...]['name']
     * @param bool   $allowSvg Whether SVG is acceptable for this slot
     * @param array  $allowed  Allowed raster extensions
     * @return string|false    Normalised extension (e.g. "jpg") or false if unsafe
     */
    function tn_upload_safe_image_ext($tmpPath, $origName, $allowSvg = true, array $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'ico'])
    {
        if (!is_string($tmpPath) || $tmpPath === '' || !is_uploaded_file($tmpPath)) {
            return false;
        }
        $size = @filesize($tmpPath);
        if ($size === false || $size <= 0 || $size > 10 * 1024 * 1024) { // 10 MB cap
            return false;
        }

        $ext = strtolower(pathinfo((string)$origName, PATHINFO_EXTENSION));

        if ($ext === 'svg') {
            if (!$allowSvg) return false;
            $svg = (string)@file_get_contents($tmpPath);
            if (stripos($svg, '<svg') === false) return false;
            // Blocks scripts, event handlers, embedded HTML, entities and any
            // href that is not an in-document "#id" or an embedded data:image.
            $dangerous = '/<\s*(script|foreignObject|iframe|embed|object|link|meta|handler|animate|set)\b'
                . '|\bon[a-z]+\s*='
                . '|javascript\s*:'
                . '|data\s*:\s*text\/html'
                . '|@import|expression\s*\('
                . '|<!ENTITY|<!DOCTYPE'
                . '|href\s*=\s*["\']\s*(?!#|data:image\/(png|jpe?g|gif|webp);)/i';
            return preg_match($dangerous, $svg) ? false : 'svg';
        }

        if (!in_array($ext, $allowed, true)) {
            return false;
        }

        $info = @getimagesize($tmpPath);
        if ($info === false || empty($info[2])) {
            return false;
        }
        $typeToExt = [
            IMAGETYPE_JPEG => ['jpg', 'jpeg'],
            IMAGETYPE_PNG  => ['png'],
            IMAGETYPE_GIF  => ['gif'],
            IMAGETYPE_ICO  => ['ico'],
        ];
        if (defined('IMAGETYPE_WEBP')) {
            $typeToExt[IMAGETYPE_WEBP] = ['webp'];
        }
        if (!isset($typeToExt[$info[2]]) || !in_array($ext, $typeToExt[$info[2]], true)) {
            return false; // content does not match the extension
        }
        return $ext === 'jpeg' ? 'jpg' : $ext;
    }
}
