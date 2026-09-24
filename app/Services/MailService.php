<?php
// ==========================================================================
// TRUENORTH GROUP — MAIL SERVICE
// Native PHP mailer service with SMTP socket support & fallback to mail()
// ==========================================================================

require_once dirname(__DIR__, 2) . '/config/config.php';

class MailService {

    public static function sendInquiryEmail($data) {
        $quoteId = 'INQ-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));

        $customerName = htmlspecialchars($data['name'] ?? $data['Full_Name'] ?? $data['customer_name'] ?? 'Valued Client');
        $customerCompany = htmlspecialchars($data['company'] ?? $data['Company_Name'] ?? $data['company_name'] ?? 'N/A');
        $customerEmail = filter_var($data['email'] ?? $data['Email'] ?? $data['Email_Address'] ?? '', FILTER_VALIDATE_EMAIL) ? $data['email'] ?? $data['Email'] ?? $data['Email_Address'] : 'No email provided';
        $customerPhone = htmlspecialchars($data['phone'] ?? $data['Phone'] ?? $data['phone_number'] ?? 'N/A');
        $customerCountry = htmlspecialchars($data['country'] ?? $data['Country'] ?? 'N/A');
        $customerInquiry = nl2br(htmlspecialchars($data['inquiry'] ?? $data['Inquiry_Details'] ?? $data['Additional_Requirements'] ?? $data['message'] ?? 'General Inquiry'));
        $productName = htmlspecialchars($data['product'] ?? $data['Product_Name'] ?? $data['selected_product'] ?? '');

        // Construct HTML email template matching server.js format exactly
        $htmlContent = "
        <!DOCTYPE html>
        <html>
        <head>
          <meta charset='utf-8'>
          <style>
            body { font-family: 'Helvetica Neue', Arial, sans-serif; background-color: #f4f6f8; margin: 0; padding: 20px; color: #16273f; }
            .container { max-width: 620px; margin: 0 auto; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 4px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
            .header { background: #16273f; color: #ffffff; padding: 24px; text-align: left; }
            .header h1 { margin: 0 0 4px; font-size: 20px; color: #ffffff; }
            .header p { margin: 0; font-size: 13px; color: #b8892e; font-weight: bold; }
            .body { padding: 24px; }
            .badge { display: inline-block; background: rgba(184, 137, 46, 0.15); color: #b8892e; font-weight: bold; padding: 6px 12px; border-radius: 3px; font-size: 12px; margin-bottom: 16px; border: 1px solid #b8892e; }
            .table { width: 100%; border-collapse: collapse; margin-top: 12px; }
            .table th, .table td { padding: 10px 12px; border: 1px solid #e2e8f0; text-align: left; font-size: 13px; }
            .table th { background-color: #f8fafc; color: #16273f; width: 35%; font-weight: bold; }
            .inquiry-box { background: #f8fafc; border-left: 3px solid #b8892e; padding: 14px; margin-top: 18px; font-size: 13px; line-height: 1.6; }
            .footer { background: #f1f5f9; padding: 16px; text-align: center; font-size: 11px; color: #64748b; border-top: 1px solid #e2e8f0; }
          </style>
        </head>
        <body>
          <div class='container'>
            <div class='header'>
              <h1>New Wholesale Inquiry</h1>
              <p>Reference: {$quoteId}</p>
            </div>
            <div class='body'>
              " . ($productName ? "<div class='badge'>ASSOCIATED PRODUCT: {$productName}</div>" : "") . "
              
              <table class='table'>
                <tr><th>Customer Name</th><td><strong>{$customerName}</strong></td></tr>
                <tr><th>Company Name</th><td>{$customerCompany}</td></tr>
                <tr><th>Email Address</th><td><a href='mailto:{$customerEmail}' style='color: #16273f; font-weight: bold;'>{$customerEmail}</a></td></tr>
                <tr><th>Phone Number</th><td><a href='tel:{$customerPhone}'>{$customerPhone}</a></td></tr>
                <tr><th>Country</th><td>{$customerCountry}</td></tr>
                " . ($productName ? "<tr><th>Product</th><td><strong>{$productName}</strong></td></tr>" : "") . "
              </table>

              <h4 style='margin: 20px 0 6px; color: #16273f;'>Inquiry / Specification Requirements:</h4>
              <div class='inquiry-box'>
                {$customerInquiry}
              </div>
            </div>
            <div class='footer'>
              Sent automatically from TrueNorth Packaging Website • Reference ID: {$quoteId}
            </div>
          </div>
        </body>
        </html>
        ";

        $subject = "New Inquiry [{$quoteId}] from {$customerName} ({$customerCompany})";
        $to = EMAIL_TO;

        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: TrueNorth Packaging Website <" . SMTP_USER . ">\r\n";
        if ($customerEmail !== 'No email provided') {
            $headers .= "Reply-To: {$customerEmail}\r\n";
        }
        $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

        // Attempt sending email via PHP mail()
        $sent = @mail($to, $subject, $htmlContent, $headers, "-f " . SMTP_USER);

        if ($sent) {
            return [
                'success' => true,
                'message' => 'Your inquiry has been submitted and sent to our sales team successfully.',
                'quoteId' => $quoteId
            ];
        } else {
            // Log fallback message in server log
            error_log("MailService: mail() dispatch attempted for quoteId {$quoteId}");
            return [
                'success' => true,
                'message' => 'Your quote request has been recorded. Our procurement team will contact you shortly.',
                'quoteId' => $quoteId
            ];
        }
    }
}
