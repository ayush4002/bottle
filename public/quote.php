<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method not allowed"]);
    exit();
}

$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Invalid JSON input"]);
    exit();
}

$quoteId = "QUOTE-" . strtoupper(substr(uniqid(), -8));
$customerEmail = isset($data['email']) ? $data['email'] : (isset($data['Email_Address']) ? $data['Email_Address'] : 'info@wetruenorthgroup.com');

$to = "info@wetruenorthgroup.com";
$subject = "New Bulk Quote Request - [" . $quoteId . "]";

$htmlContent = "<h2>New Quote Request: " . $quoteId . "</h2>";
$htmlContent .= "<table style='border-collapse: collapse; width: 100%; max-width: 600px;'>";

foreach ($data as $key => $value) {
    if (strpos($key, '_') === 0) continue;
    $formattedKey = str_replace('_', ' ', $key);
    $htmlContent .= "<tr>
        <td style='padding: 10px; border: 1px solid #ddd; font-weight: bold; background: #f9f9f9;'>" . htmlspecialchars($formattedKey) . "</td>
        <td style='padding: 10px; border: 1px solid #ddd;'>" . htmlspecialchars($value) . "</td>
    </tr>";
}
$htmlContent .= "</table>";

$domain = "wetruenorthgroup.com";
$messageId = "<" . md5(uniqid(time())) . "@" . $domain . ">";

$headers = "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
$headers .= "From: TrueNorth Group <info@wetruenorthgroup.com>\r\n";
$headers .= "Reply-To: " . $customerEmail . "\r\n";
$headers .= "Return-Path: info@wetruenorthgroup.com\r\n";
$headers .= "Message-ID: " . $messageId . "\r\n";
$headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
$headers .= "X-Priority: 3\r\n";

if (mail($to, $subject, $htmlContent, $headers, "-f info@wetruenorthgroup.com")) {
    http_response_code(200);
    echo json_encode([
        "success" => true,
        "message" => "Your quote request has been submitted successfully. Our team will contact you shortly.",
        "quoteId" => $quoteId
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Failed to send email. Please check cPanel mail configuration."
    ]);
}
?>
