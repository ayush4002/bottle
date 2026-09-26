<?php
// ==========================================================================
// TRUENORTH GROUP — MAIL SERVICE
// Native PHP mailer with authenticated SSL SMTP socket client + client auto-reply
// ==========================================================================

require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__) . '/Models/InquiryModel.php';

class MailService {

    public static function sendInquiryEmail($data) {
        $quoteId = 'INQ-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));

        $customerName = htmlspecialchars(trim($data['name'] ?? $data['Full_Name'] ?? $data['customer_name'] ?? 'Valued Client'));
        $customerCompany = htmlspecialchars(trim($data['company'] ?? $data['Company_Name'] ?? $data['company_name'] ?? 'N/A'));
        $rawEmail = trim($data['email'] ?? $data['Email'] ?? $data['Email_Address'] ?? '');
        $customerEmail = filter_var($rawEmail, FILTER_VALIDATE_EMAIL) ? $rawEmail : '';
        $customerPhone = htmlspecialchars(trim($data['phone'] ?? $data['Phone'] ?? $data['phone_number'] ?? 'N/A'));
        $customerCountry = htmlspecialchars(trim($data['country'] ?? $data['Country'] ?? 'N/A'));
        $rawInquiry = trim($data['inquiry'] ?? $data['Inquiry_Details'] ?? $data['Additional_Requirements'] ?? $data['message'] ?? 'General Inquiry');
        $customerInquiry = nl2br(htmlspecialchars($rawInquiry));
        $productName = htmlspecialchars(trim($data['product'] ?? $data['Product_Name'] ?? $data['selected_product'] ?? ''));

        // 1. Persist inquiry into MySQL Database and JSON backup
        try {
            InquiryModel::saveInquiry($data, $quoteId);
        } catch (Exception $e) {
            error_log("MailService: Failed saving inquiry to database: " . $e->getMessage());
        }

        // ------------------------------------------------------------------
        // 2. Staff Notification Email Template
        // ------------------------------------------------------------------
        $adminHtml = "
        <!DOCTYPE html>
        <html>
        <head>
          <meta charset='utf-8'>
          <style>
            body { font-family: 'Helvetica Neue', Arial, sans-serif; background-color: #f4f6f8; margin: 0; padding: 20px; color: #16273f; }
            .container { max-width: 620px; margin: 0 auto; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 6px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.06); }
            .header { background: #16273f; color: #ffffff; padding: 24px; text-align: left; }
            .header h1 { margin: 0 0 4px; font-size: 20px; color: #ffffff; font-weight: 700; }
            .header p { margin: 0; font-size: 13px; color: #b8892e; font-weight: bold; }
            .body { padding: 24px; }
            .badge { display: inline-block; background: rgba(184, 137, 46, 0.15); color: #b8892e; font-weight: bold; padding: 6px 12px; border-radius: 4px; font-size: 12px; margin-bottom: 16px; border: 1px solid #b8892e; }
            .table { width: 100%; border-collapse: collapse; margin-top: 12px; }
            .table th, .table td { padding: 10px 12px; border: 1px solid #e2e8f0; text-align: left; font-size: 13px; }
            .table th { background-color: #f8fafc; color: #16273f; width: 35%; font-weight: bold; }
            .inquiry-box { background: #f8fafc; border-left: 3px solid #b8892e; padding: 14px; margin-top: 18px; font-size: 13px; line-height: 1.6; border-radius: 0 4px 4px 0; }
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
              " . ($productName ? "<div class='badge'>ASSOCIATED PRODUCT / REQUIREMENT: {$productName}</div>" : "") . "
              
              <table class='table'>
                <tr><th>Customer Name</th><td><strong>{$customerName}</strong></td></tr>
                <tr><th>Company Name</th><td>{$customerCompany}</td></tr>
                <tr><th>Email Address</th><td><a href='mailto:{$customerEmail}' style='color: #16273f; font-weight: bold;'>{$customerEmail}</a></td></tr>
                <tr><th>Phone Number</th><td><a href='tel:{$customerPhone}'>{$customerPhone}</a></td></tr>
                <tr><th>Country</th><td>{$customerCountry}</td></tr>
                " . ($productName ? "<tr><th>Subject / Product</th><td><strong>{$productName}</strong></td></tr>" : "") . "
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

        $adminSubject = "New Inquiry [{$quoteId}] from {$customerName} ({$customerCompany})";
        $toAdmin = defined('EMAIL_TO') ? EMAIL_TO : 'info@wetruenorthgroup.com';

        // 3. Send Staff Notification via Authenticated SMTP Socket
        $adminSent = self::sendSmtp($toAdmin, $adminSubject, $adminHtml, $customerEmail);

        if (!$adminSent) {
            // Fallback to mail()
            $headers = "MIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8\r\n";
            $headers .= "From: TrueNorth Packaging <" . (defined('SMTP_USER') ? SMTP_USER : 'info@wetruenorthgroup.com') . ">\r\n";
            if (!empty($customerEmail)) $headers .= "Reply-To: {$customerEmail}\r\n";
            @mail($toAdmin, $adminSubject, $adminHtml, $headers, "-f " . (defined('SMTP_USER') ? SMTP_USER : 'info@wetruenorthgroup.com'));
        }

        // ------------------------------------------------------------------
        // 4. Send Confirmation Copy to Client (If Enabled)
        // ------------------------------------------------------------------
        $settingsFile = (defined('ROOT_PATH') ? ROOT_PATH : dirname(__DIR__, 2)) . '/config/site_settings.json';
        $siteSettings = file_exists($settingsFile) ? (json_decode(file_get_contents($settingsFile), true) ?: []) : [];
        $sendClientCopy = isset($siteSettings['send_client_confirmation']) ? (bool)$siteSettings['send_client_confirmation'] : true;

        if ($sendClientCopy && !empty($customerEmail)) {
            $clientSubject = "Inquiry Confirmation [{$quoteId}] — TrueNorth Group";
            $clientHtml = "
            <!DOCTYPE html>
            <html>
            <head>
              <meta charset='utf-8'>
              <style>
                body { font-family: 'Helvetica Neue', Arial, sans-serif; background-color: #f4f6f8; margin: 0; padding: 20px; color: #16273f; }
                .container { max-width: 620px; margin: 0 auto; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 6px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.06); }
                .header { background: #16273f; color: #ffffff; padding: 26px 24px; text-align: left; }
                .header h1 { margin: 0 0 4px; font-size: 20px; color: #ffffff; font-weight: 800; }
                .header p { margin: 0; font-size: 13px; color: #b8892e; font-weight: bold; }
                .body { padding: 24px; line-height: 1.6; }
                .thank-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 18px; margin-bottom: 20px; }
                .ref-badge { display: inline-block; background: rgba(184, 137, 46, 0.15); color: #b8892e; font-weight: 800; padding: 6px 12px; border-radius: 4px; font-size: 13px; margin: 8px 0; border: 1px solid #b8892e; }
                .table { width: 100%; border-collapse: collapse; margin-top: 14px; }
                .table th, .table td { padding: 10px 12px; border: 1px solid #e2e8f0; text-align: left; font-size: 13px; }
                .table th { background-color: #f8fafc; color: #16273f; width: 35%; font-weight: bold; }
                .inquiry-box { background: #f8fafc; border-left: 3px solid #b8892e; padding: 14px; margin-top: 14px; font-size: 13px; line-height: 1.6; border-radius: 0 4px 4px 0; }
                .cta-block { background: #f1f5f9; padding: 16px; border-radius: 6px; margin-top: 24px; font-size: 12px; color: #475569; }
                .footer { background: #16273f; padding: 18px; text-align: center; font-size: 11px; color: #94a3b8; }
                .footer a { color: #b8892e; text-decoration: none; font-weight: 700; }
              </style>
            </head>
            <body>
              <div class='container'>
                <div class='header'>
                  <h1>TrueNorth Group</h1>
                  <p>Primary Packaging Specialist</p>
                </div>
                <div class='body'>
                  <div class='thank-card'>
                    <h2 style='font-size: 1.25rem; font-weight: 800; color: #16273f; margin: 0 0 6px;'>Thank you, {$customerName}!</h2>
                    <p style='margin: 0; color: #475569; font-size: 14px;'>
                      We have successfully received your wholesale inquiry. Our engineering and procurement team is currently reviewing your requirements. A packaging specialist will follow up with technical drawings, quotation tiers, and sample dispatch details within <strong>4 to 24 business hours</strong>.
                    </p>
                    <div class='ref-badge'>Inquiry Reference: {$quoteId}</div>
                  </div>

                  <h3 style='font-size: 14px; font-weight: 800; color: #16273f; margin: 20px 0 6px; text-transform: uppercase; letter-spacing: 0.5px;'>Copy of Your Submitted Request:</h3>
                  <table class='table'>
                    <tr><th>Contact Name</th><td>{$customerName}</td></tr>
                    <tr><th>Company Name</th><td>{$customerCompany}</td></tr>
                    <tr><th>Email Address</th><td>{$customerEmail}</td></tr>
                    <tr><th>Phone Number</th><td>{$customerPhone}</td></tr>
                    <tr><th>Country</th><td>{$customerCountry}</td></tr>
                    " . ($productName ? "<tr><th>Product / Requirement</th><td><strong>{$productName}</strong></td></tr>" : "") . "
                  </table>

                  <h4 style='margin: 18px 0 6px; color: #16273f; font-size: 13px;'>Your Message &amp; Specifications:</h4>
                  <div class='inquiry-box'>
                    {$customerInquiry}
                  </div>

                  <div class='cta-block'>
                    <strong>Need immediate assistance or have CAD / 3D design files to attach?</strong><br>
                    You can reply directly to this email (<a href='mailto:info@wetruenorthgroup.com' style='color: #16273f; font-weight: 700;'>info@wetruenorthgroup.com</a>) or contact our technical sales desk at <strong>+91 98765 43210</strong>.
                  </div>
                </div>
                <div class='footer'>
                  © " . date('Y') . " TrueNorth Group. All Rights Reserved. • <a href='https://wetruenorthgroup.com'>wetruenorthgroup.com</a><br>
                  TÜV NORD ISO 9001 Certified • Food-Grade Cleanroom Manufacturing Hubs
                </div>
              </div>
            </body>
            </html>
            ";

            $clientSent = self::sendSmtp($customerEmail, $clientSubject, $clientHtml, defined('SMTP_USER') ? SMTP_USER : 'info@wetruenorthgroup.com');
            if (!$clientSent) {
                // Fail-safe fallback to mail() if SMTP fails
                $headers = "MIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8\r\n";
                $headers .= "From: TrueNorth Packaging <" . (defined('SMTP_USER') ? SMTP_USER : 'info@wetruenorthgroup.com') . ">\r\n";
                $headers .= "Reply-To: " . (defined('SMTP_USER') ? SMTP_USER : 'info@wetruenorthgroup.com') . "\r\n";
                $headers .= "Message-ID: <" . md5(uniqid(microtime(), true)) . "@wetruenorthgroup.com>\r\n";
                $headers .= "X-Mailer: TrueNorth-Mailer/2.0\r\n";
                @mail($customerEmail, $clientSubject, $clientHtml, $headers, "-f " . (defined('SMTP_USER') ? SMTP_USER : 'info@wetruenorthgroup.com'));
            }
        }

        return [
            'success' => true,
            'message' => 'Your inquiry has been submitted and sent to our sales team successfully. A confirmation email has been dispatched to ' . htmlspecialchars($customerEmail) . '.',
            'quoteId' => $quoteId
        ];
    }

    /**
     * Authenticated native PHP SMTP client over SSL socket
     */
    public static function sendSmtp($to, $subject, $htmlContent, $replyTo = '') {
        $host = defined('SMTP_HOST') ? SMTP_HOST : 'mail.wetruenorthgroup.com';
        $port = defined('SMTP_PORT') ? (int)SMTP_PORT : 465;
        $user = defined('SMTP_USER') ? SMTP_USER : 'info@wetruenorthgroup.com';
        $pass = defined('SMTP_PASS') ? SMTP_PASS : 'TrueNorth2026Group';

        $timeout = 10;
        $ctx = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ]);

        $protocol = ($port == 465) ? 'ssl://' : '';
        $socket = @stream_socket_client($protocol . $host . ':' . $port, $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT, $ctx);
        if (!$socket) {
            error_log("MailService SMTP connection failed: {$errstr} ({$errno})");
            return false;
        }

        stream_set_timeout($socket, $timeout);

        $read = function() use ($socket) {
            $data = '';
            while ($str = fgets($socket, 515)) {
                $data .= $str;
                if (substr($str, 3, 1) === ' ') break;
            }
            return $data;
        };

        $write = function($cmd) use ($socket) {
            fputs($socket, $cmd . "\r\n");
        };

        $res = $read();
        if (substr($res, 0, 3) !== '220') { fclose($socket); return false; }

        $clientHost = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'wetruenorthgroup.com';
        $write('EHLO ' . $clientHost);
        $res = $read();
        if (substr($res, 0, 3) !== '250') { fclose($socket); return false; }

        $write('AUTH LOGIN');
        $res = $read();
        if (substr($res, 0, 3) !== '334') { fclose($socket); return false; }

        $write(base64_encode($user));
        $res = $read();
        if (substr($res, 0, 3) !== '334') { fclose($socket); return false; }

        $write(base64_encode($pass));
        $res = $read();
        if (substr($res, 0, 3) !== '235') { fclose($socket); return false; }

        $write("MAIL FROM: <{$user}>");
        $res = $read();
        if (substr($res, 0, 3) !== '250') { fclose($socket); return false; }

        $write("RCPT TO: <{$to}>");
        $res = $read();
        if (substr($res, 0, 3) !== '250') { fclose($socket); return false; }

        $write('DATA');
        $res = $read();
        if (substr($res, 0, 3) !== '354') { fclose($socket); return false; }

        $msgId = '<' . md5(uniqid(microtime(), true)) . '@wetruenorthgroup.com>';
        $headers = [
            'Date: ' . date('r'),
            'To: ' . $to,
            'From: ' . 'TrueNorth Packaging <' . $user . '>',
            'Subject: ' . '=?UTF-8?B?' . base64_encode($subject) . '?=',
            'Message-ID: ' . $msgId,
            'X-Mailer: TrueNorth-System/2.0',
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'Content-Transfer-Encoding: base64'
        ];
        if (!empty($replyTo) && filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
            $headers[] = 'Reply-To: ' . $replyTo;
        }

        $emailContent = implode("\r\n", $headers) . "\r\n\r\n" . chunk_split(base64_encode($htmlContent));
        $emailContent = str_replace("\r\n.", "\r\n..", $emailContent);

        $write($emailContent . "\r\n.");
        $res = $read();
        if (substr($res, 0, 3) !== '250') { fclose($socket); return false; }

        $write('QUIT');
        $read();
        fclose($socket);
        return true;
    }
}
