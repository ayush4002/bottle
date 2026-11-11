<?php
// ==========================================================================
// TRUENORTH GROUP — FULL-PAGE OUTPUT CACHE
// Eliminates repeated PHP + MySQL execution for public read-only pages.
// Stored as config/cache/page/<md5>.cache (envelope line + raw HTML body).
// TTL is a 5-minute safety net; the authoritative invalidation is
// PageCache::flush(), wired into SeoModel::flushCache() so every admin
// save (products, blog, SEO settings, media) purges the page cache.
// ==========================================================================

class PageCache
{
    const TTL = 300; // seconds — safety net only; admin saves flush immediately
    const DIR_NAME = 'page';

    private static $skip = false;
    private static $status = 200;

    private static function dir()
    {
        static $dir = null;
        if ($dir === null) {
            $base = defined('ROOT_PATH') ? ROOT_PATH : dirname(__DIR__);
            $dir = rtrim($base, '/\\') . '/config/cache/' . self::DIR_NAME;
            if (!is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }
        }
        return $dir;
    }

    /** Is a full-page cache appropriate for this request? */
    public static function enabled()
    {
        if (self::$skip) return false;
        if (defined('APP_ENV') && APP_ENV === 'development') return false;
        if (strcasecmp($_SERVER['REQUEST_METHOD'] ?? 'GET', 'GET') !== 0) return false;
        if (!empty($_POST)) return false;
        // Never serve stale markup to a logged-in admin session
        if (session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION['admin_logged_in'])) {
            return false;
        }
        return true;
    }

    /** Force disable for the current request (e.g. conditional routes). */
    public static function skip()
    {
        self::$skip = true;
    }

    /**
     * Build a stable cache key from route + the only public GET params
     * that change page content. UTM/tracking params are deliberately
     * excluded so cached copies are shared across campaign links.
     */
    public static function keyFor($route, array $params = null)
    {
        $params = $params === null ? $_GET : $params;
        $keep = array_intersect_key($params, array_flip(['category', 'series', 'industry', 'q', 'page']));
        // Free-text search results are not cached (unbounded key space)
        if (!empty($keep['q'])) {
            self::$skip = true;
        }
        ksort($keep);
        return md5($route . '|' . http_build_query($keep));
    }

    /**
     * @return string|null Cached HTML (with status stashed), or null on miss.
     */
    public static function get($key)
    {
        if (!self::enabled()) return null;
        $f = self::dir() . '/' . $key . '.cache';
        if (!is_file($f) || (time() - filemtime($f)) > self::TTL) return null;
        $raw = @file_get_contents($f);
        if ($raw === false || $raw === '') return null;
        $nl = strpos($raw, "\n");
        if ($nl === false) return null;
        $meta = json_decode(substr($raw, 0, $nl), true);
        if (!is_array($meta) || !isset($meta['st'])) return null;
        self::$status = (int) $meta['st'];
        return substr($raw, $nl + 1);
    }

    /** Emit cached HTML with the original status code and terminate. */
    public static function serve($html, $status = null)
    {
        if ($status === null) $status = self::$status;
        http_response_code($status);
        header('Content-Type: text/html; charset=UTF-8');
        header('Cache-Control: public, max-age=60');
        header('X-Page-Cache: HIT');
        echo $html;
        exit;
    }

    /** Store freshly rendered HTML (200 only; skip error-sized output; capped file count). */
    public static function put($key, $html, $status)
    {
        if (!self::enabled()) return;
        // Only successful pages are cached: caching 404s for arbitrary URLs/IDs lets anyone fill the disk
        if ($status !== 200) return;
        if (strlen($html) < 256) return;
        $files = glob(self::dir() . '/*.cache') ?: [];
        if (count($files) > 3000) {
            return;
        }
        $meta = json_encode(['st' => $status, 'tm' => time()]);
        @file_put_contents(self::dir() . '/' . $key . '.cache', $meta . "\n" . $html, LOCK_EX);
    }

    /** Purge every cached page. Called from SeoModel::flushCache(). */
    public static function flush()
    {
        $dir = self::dir();
        foreach (glob($dir . '/*.cache') ?: [] as $f) {
            @unlink($f);
        }
        @file_put_contents($dir . '/.flushed', (string) time());
    }
}
