<?php
// ==========================================================================
// TRUENORTH GROUP — QUOTE CONTROLLER
// Controller handling wholesale RFQ quote & inquiry form submissions
// ==========================================================================

require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__) . '/Services/MailService.php';

class QuoteController {

    public static function handleInquiry() {
        // Enforce JSON header
        header("Content-Type: application/json; charset=UTF-8");
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: Content-Type");

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(["success" => false, "message" => "Method Not Allowed"]);
            exit();
        }

        // Parse JSON or form POST input
        $rawInput = file_get_contents('php://input');
        $data = json_decode($rawInput, true);

        if (!$data && !empty($_POST)) {
            $data = $_POST;
        }

        if (!$data) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Invalid or empty form submission."]);
            exit();
        }

        // Validation
        $email = $data['email'] ?? $data['Email'] ?? $data['Email_Address'] ?? '';
        if (empty($email)) {
            http_response_code(422);
            echo json_encode(["success" => false, "message" => "Email address is required."]);
            exit();
        }

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
