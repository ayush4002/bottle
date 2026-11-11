<?php
// ==========================================================================
// TRUENORTH GROUP — QUOTE CONTROLLER
// Controller handling wholesale RFQ quote & inquiry form submissions
// Features anti-spam honeypot checks and per-IP rate limiting defense
// ==========================================================================

require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__) . '/Services/MailService.php';

class QuoteController {

    public static function handleInquiry() {
        // Enforce JSON header
        header("Content-Type: application/json; charset=UTF-8");
        header("Cache-Control: no-store");

        // Same-origin only: reject cross-site browser submissions (no CORS wildcard)
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        if ($origin !== '') {
            $originHost = strtolower((string)parse_url($origin, PHP_URL_HOST));
            $allowedHosts = ['wetruenorthgroup.com', 'www.wetruenorthgroup.com', 'localhost', '127.0.0.1'];
            if (!in_array($originHost, $allowedHosts, true)) {
                http_response_code(403);
                echo json_encode(["success" => false, "message" => "Cross-origin submissions are not allowed."]);
                exit();
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(["success" => false, "message" => "Method Not Allowed"]);
            exit();
        }

        // 1. IP-Based Rate Limiting (Defense against automated form spamming — Max 5/hr)
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $cacheDir = dirname(__DIR__, 2) . '/config/cache';
        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0755, true);
        }
        $rateFile = $cacheDir . '/quote_rate_' . md5($ip) . '.json';
        $now = time();
        $rateData = ['count' => 0, 'first_attempt' => $now];

        if (file_exists($rateFile)) {
            $decoded = json_decode(file_get_contents($rateFile), true);
            if (is_array($decoded)) {
                $rateData = $decoded;
            }
        }

        // Reset if window of 1 hour (3600 seconds) has elapsed
        if ($now - ($rateData['first_attempt'] ?? $now) > 3600) {
            $rateData = ['count' => 0, 'first_attempt' => $now];
        }

        if (($rateData['count'] ?? 0) >= 5) {
            http_response_code(429);
            echo json_encode([
                "success" => false,
                "message" => "Rate limit exceeded. Maximum 5 quote inquiries allowed per hour. For urgent requests, please contact our commercial division directly at +91 98102 20761 or info@wetruenorthgroup.com."
            ]);
            exit();
        }

        // Parse JSON or form POST input (max 64 KB)
        $rawInput = file_get_contents('php://input', false, null, 0, 65536);
        $data = json_decode($rawInput, true);

        if (!is_array($data) && !empty($_POST)) {
            $data = $_POST;
        }

        if (is_array($data)) {
            // Only flat scalar fields are accepted
            $data = array_filter($data, 'is_scalar');
        }

        if (empty($data) || !is_array($data)) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Invalid or empty form submission."]);
            exit();
        }

        // 2. Honeypot Anti-Spam Check (Hidden fields meant for spambots)
        if (!empty($data['website_url_hp']) || !empty($data['hp_field']) || !empty($data['comment_hp'])) {
            // Fake success to silently discard bot submissions without logging or email dispatch
            http_response_code(200);
            echo json_encode([
                "success" => true,
                "message" => "Inquiry received. A TrueNorth commercial specialist will review your requirements."
            ]);
            exit();
        }

        // Validation
        $email = trim((string)($data['email'] ?? $data['Email'] ?? $data['Email_Address'] ?? ''));
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254) {
            http_response_code(422);
            echo json_encode(["success" => false, "message" => "A valid email address is required."]);
            exit();
        }

        // Increment rate limit count
        $rateData['count'] = ($rateData['count'] ?? 0) + 1;
        file_put_contents($rateFile, json_encode($rateData), LOCK_EX);

        $result = MailService::sendInquiryEmail($data);

        if ($result['success']) {
            http_response_code(200);
            echo json_encode($result);
        } else {
            http_response_code(500);
            echo json_encode($result);
        }
        exit();
    }
}
