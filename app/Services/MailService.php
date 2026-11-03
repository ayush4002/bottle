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
        $targetVolume = htmlspecialchars(trim($data['target_volume'] ?? $data['capacity'] ?? ''));
        $material = htmlspecialchars(trim($data['material'] ?? $data['material_preference'] ?? ''));
        $quantity = htmlspecialchars(trim($data['estimated_quantity'] ?? $data['quantity'] ?? ''));
        $sourcePage = htmlspecialchars(trim($data['source_page'] ?? ''));

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
                " . ($targetVolume ? "<tr><th>Target Volume / Capacity</th><td><strong>{$targetVolume}</strong></td></tr>" : "") . "
                " . ($material ? "<tr><th>Material Preference</th><td><strong>{$material}</strong></td></tr>" : "") . "
                " . ($quantity ? "<tr><th>Estimated Initial Run (MOQ)</th><td><strong>{$quantity}</strong></td></tr>" : "") . "
                " . ($sourcePage ? "<tr><th>Inquiry Source</th><td><span style='color: #64748b;'>{$sourcePage}</span></td></tr>" : "") . "
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

        $cfg = self::getEmailSettings();
        $company = class_exists('ProductModel') ? ProductModel::getCompanyInfo() : [];
        $siteName = !empty($cfg['from_name']) ? $cfg['from_name'] : ($company['name'] ?? 'TrueNorth Group');
        $siteTagline = !empty($company['tagline']) ? $company['tagline'] : 'Primary Packaging Specialist';
        $sitePhone = !empty($company['phone']) ? $company['phone'] : '+91 9810220761';
        $toAdmin = $cfg['contact_email'];

        $adminSubject = "New Inquiry [{$quoteId}] from {$customerName} ({$customerCompany})";

        // 3. Send Staff Notification via Authenticated SMTP Socket
        $adminSent = self::sendSmtp($toAdmin, $adminSubject, $adminHtml, $customerEmail);

        if (!$adminSent) {
            // Fallback to mail()
            $headers = "MIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8\r\n";
            $headers .= "From: " . '=?UTF-8?B?' . base64_encode($siteName) . '?=' . " <" . $cfg['from_email'] . ">\r\n";
            if (!empty($customerEmail)) $headers .= "Reply-To: {$customerEmail}\r\n";
            @mail($toAdmin, $adminSubject, $adminHtml, $headers, "-f " . $cfg['from_email']);
        }

        // ------------------------------------------------------------------
        // 4. Send Confirmation & Acknowledgement Copy to Client
        // ------------------------------------------------------------------
        $sendClientCopy = !empty($cfg['send_client_confirmation']);

        if ($sendClientCopy && !empty($customerEmail)) {
            $clientSubject = "Inquiry Acknowledgement [{$quoteId}] — " . $siteName;
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
                  <h1>{$siteName}</h1>
                  <p>{$siteTagline}</p>
                </div>
                <div class='body'>
                  <div class='thank-card'>
                    <h2 style='font-size: 1.25rem; font-weight: 800; color: #16273f; margin: 0 0 6px;'>Thank you, {$customerName}!</h2>
                    <p style='margin: 0; color: #475569; font-size: 14px;'>
                      We have successfully received your wholesale inquiry. Our engineering and procurement team is currently reviewing your requirements. A packaging specialist will follow up with technical drawings, quotation tiers, and sample dispatch details within <strong>1 business day</strong>.
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
                    " . ($targetVolume ? "<tr><th>Target Volume / Capacity</th><td><strong>{$targetVolume}</strong></td></tr>" : "") . "
                    " . ($material ? "<tr><th>Preferred Material</th><td><strong>{$material}</strong></td></tr>" : "") . "
                    " . ($quantity ? "<tr><th>Estimated Initial Run (MOQ)</th><td><strong>{$quantity}</strong></td></tr>" : "") . "
                  </table>

                  <h4 style='margin: 18px 0 6px; color: #16273f; font-size: 13px;'>Your Message &amp; Specifications:</h4>
                  <div class='inquiry-box'>
                    {$customerInquiry}
                  </div>

                  <div class='cta-block'>
                    <strong>Need immediate assistance or have CAD / 3D design files to attach?</strong><br>
                    You can reply directly to this email (<a href='mailto:{$cfg['from_email']}' style='color: #16273f; font-weight: 700;'>{$cfg['from_email']}</a>) or contact our technical sales desk at <strong>{$sitePhone}</strong>.
                  </div>
                </div>
                <div class='footer'>
                  © " . date('Y') . " {$siteName}. All Rights Reserved. • <a href='https://wetruenorthgroup.com'>wetruenorthgroup.com</a><br>
                  TÜV NORD ISO 9001 Certified • Food-Grade Cleanroom Manufacturing Hubs
                </div>
              </div>
            </body>
            </html>
            ";

            $clientSent = self::sendSmtp($customerEmail, $clientSubject, $clientHtml, $cfg['reply_to']);
            if (!$clientSent) {
                // Fail-safe fallback to mail() if SMTP fails
                $headers = "MIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8\r\n";
                $headers .= "From: " . '=?UTF-8?B?' . base64_encode($siteName) . '?=' . " <" . $cfg['from_email'] . ">\r\n";
                $headers .= "Reply-To: " . $cfg['reply_to'] . "\r\n";
                $headers .= "Message-ID: <" . md5(uniqid(microtime(), true)) . "@wetruenorthgroup.com>\r\n";
                $headers .= "X-Mailer: TrueNorth-Mailer/2.0\r\n";
                @mail($customerEmail, $clientSubject, $clientHtml, $headers, "-f " . $cfg['from_email']);
            }
        }

        return [
            'success' => true,
            'message' => 'Your inquiry has been submitted successfully! An acknowledgement confirmation email has been dispatched to ' . htmlspecialchars($customerEmail) . '.',
            'quoteId' => $quoteId
        ];
    }

    /**
     * Authenticated native PHP SMTP client over SSL socket
     */
    public static function sendSmtp($to, $subject, $htmlContent, $replyTo = '') {
        $cfg = self::getEmailSettings();
        $host = $cfg['host'] ?: (defined('SMTP_HOST') ? SMTP_HOST : 'mail.wetruenorthgroup.com');
        $port = (int)($cfg['port'] ?: (defined('SMTP_PORT') ? SMTP_PORT : 465));
        $user = $cfg['user'] ?: (defined('SMTP_USER') ? SMTP_USER : 'info@wetruenorthgroup.com');
        $pass = $cfg['pass'] ?: (defined('SMTP_PASS') ? SMTP_PASS : 'TrueNorth2026Group');
        $fromName = $cfg['from_name'] ?: 'TrueNorth Group';
        $fromEmail = $cfg['from_email'] ?: $user;

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
            'From: ' . '=?UTF-8?B?' . base64_encode($fromName) . '?= <' . $fromEmail . '>',
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

    /**
     * Get dynamic SMTP and outgoing email settings from site_settings.json or fallback constants
     */
    public static function getEmailSettings() {
        $settingsFile = (defined('ROOT_PATH') ? ROOT_PATH : dirname(__DIR__, 2)) . '/config/site_settings.json';
        $json = file_exists($settingsFile) ? (json_decode(file_get_contents($settingsFile), true) ?: []) : [];

        return [
            'mode'       => $json['mail_mode'] ?? 'smtp', // 'smtp' or 'mail'
            'host'       => $json['smtp_host'] ?? (defined('SMTP_HOST') ? SMTP_HOST : 'mail.wetruenorthgroup.com'),
            'port'       => (int)($json['smtp_port'] ?? (defined('SMTP_PORT') ? SMTP_PORT : 465)),
            'user'       => $json['smtp_user'] ?? (defined('SMTP_USER') ? SMTP_USER : 'info@wetruenorthgroup.com'),
            'pass'       => $json['smtp_pass'] ?? (defined('SMTP_PASS') ? SMTP_PASS : 'TrueNorth2026Group'),
            'from_name'  => $json['from_name'] ?? ($json['site_name'] ?? 'TrueNorth Group'),
            'from_email' => $json['from_email'] ?? ($json['contact_email'] ?? (defined('SMTP_USER') ? SMTP_USER : 'info@wetruenorthgroup.com')),
            'reply_to'   => $json['reply_to'] ?? ($json['contact_email'] ?? 'info@wetruenorthgroup.com'),
            'contact_email' => $json['contact_email'] ?? (defined('EMAIL_TO') ? EMAIL_TO : 'info@wetruenorthgroup.com'),
            'send_client_confirmation' => isset($json['send_client_confirmation']) ? (int)$json['send_client_confirmation'] : 1
        ];
    }

    /**
     * Send personalized official response / quote reply to a client inquiry
     */
    public static function sendCustomReply($clientEmail, $clientName, $subject, $messageBody, $quoteId = '', $adminUser = 'TrueNorth Sales') {
        $cfg = self::getEmailSettings();
        $company = class_exists('ProductModel') ? ProductModel::getCompanyInfo() : [];
        $siteName = !empty($cfg['from_name']) ? $cfg['from_name'] : ($company['name'] ?? 'TrueNorth Group');
        $siteTagline = !empty($company['tagline']) ? $company['tagline'] : 'Global Primary Packaging Specialists & Precision Manufacturers';
        $sitePhone = !empty($company['phone']) ? $company['phone'] : '+91 98102 20761';
        $siteHq = !empty($company['hq']) ? nl2br(htmlspecialchars($company['hq'])) : '#201, Vardhaman Trade Towers, Rohit Kunj, LSC Pitampura, Delhi-110034';

        $cleanEmail = filter_var(trim($clientEmail), FILTER_VALIDATE_EMAIL);

        if (!$cleanEmail) {
            return ['success' => false, 'message' => 'Invalid client email address.'];
        }

        $safeName = htmlspecialchars(trim($clientName ?: 'Valued Client'));
        $safeSubject = trim($subject ?: "Quotation & Specification Reply — " . $siteName);
        $safeRef = htmlspecialchars(trim($quoteId ?: 'INQ'));
        $formattedBody = nl2br(htmlspecialchars(trim($messageBody)));

        $html = "<!DOCTYPE html>
        <html>
        <head>
          <meta charset='utf-8'>
          <meta name='viewport' content='width=device-width, initial-scale=1.0'>
          <title>" . htmlspecialchars($safeSubject) . "</title>
          <style>
            body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f1f5f9; margin: 0; padding: 24px 12px; color: #1e293b; line-height: 1.6; }
            .email-wrapper { max-width: 640px; margin: 0 auto; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 14px rgba(0,0,0,0.06); }
            .email-header { background: #16273f; padding: 28px 32px; text-align: left; border-bottom: 3px solid #b8892e; }
            .brand-title { color: #ffffff; font-size: 22px; font-weight: 800; letter-spacing: 0.5px; margin: 0 0 4px; }
            .brand-sub { color: #b8892e; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.8px; margin: 0; }
            .email-body { padding: 32px; background: #ffffff; }
            .ref-badge { display: inline-block; background: rgba(184, 137, 46, 0.12); color: #b8892e; font-size: 12px; font-weight: 800; padding: 4px 10px; border-radius: 4px; border: 1px solid rgba(184,137,46,0.3); margin-bottom: 20px; }
            .greeting { font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 16px; }
            .message-box { font-size: 14.5px; color: #334155; line-height: 1.7; margin-bottom: 28px; }
            .signoff-box { background: #f8fafc; border: 1px solid #e2e8f0; border-left: 4px solid #b8892e; padding: 18px 20px; border-radius: 0 8px 8px 0; margin-top: 24px; }
            .signoff-title { font-weight: 800; font-size: 14px; color: #16273f; margin-bottom: 4px; }
            .signoff-meta { font-size: 12.5px; color: #64748b; line-height: 1.5; }
            .email-footer { background: #f8fafc; padding: 20px 32px; text-align: center; font-size: 12px; color: #94a3b8; border-top: 1px solid #e2e8f0; }
          </style>
        </head>
        <body>
          <div class='email-wrapper'>
            <div class='email-header'>
              <div class='brand-title'>{$siteName}</div>
              <div class='brand-sub'>{$siteTagline}</div>
            </div>
            <div class='email-body'>
              <div class='ref-badge'>INQUIRY REFERENCE: {$safeRef}</div>
              <div class='greeting'>Dear {$safeName},</div>
              <div class='message-box'>
                {$formattedBody}
              </div>
              <div class='signoff-box'>
                <div class='signoff-title'>{$siteName} — Executive Sales & Engineering</div>
                <div class='signoff-meta'>
                  {$siteHq}<br/>
                  Direct Hotline / WhatsApp: <strong>{$sitePhone}</strong><br/>
                  Email: <a href='mailto:{$cfg['from_email']}' style='color:#b8892e; text-decoration:none;'>{$cfg['from_email']}</a> &bull; Web: <a href='https://wetruenorthgroup.com' style='color:#16273f; text-decoration:none;'>wetruenorthgroup.com</a>
                </div>
              </div>
            </div>
            <div class='email-footer'>
              &copy; " . date('Y') . " {$siteName}. This email was dispatched regarding quotation reference {$safeRef}.
            </div>
          </div>
        </body>
        </html>";

        // Try authenticated SMTP first
        $sent = false;
        if ($cfg['mode'] === 'smtp') {
            $sent = self::sendSmtp($cleanEmail, $safeSubject, $html, $cfg['reply_to']);
        }

        // Fallback to PHP native mail()
        if (!$sent) {
            $headers = "MIME-Version: 1.0

";
            $headers .= "Content-Type: text/html; charset=UTF-8

";
            $headers .= "From: " . '=?UTF-8?B?' . base64_encode($cfg['from_name']) . '?=' . " <" . $cfg['from_email'] . ">

";
            $headers .= "Reply-To: " . $cfg['reply_to'] . "

";
            $headers .= "X-Mailer: TrueNorth-CMS/2.0

";

            $sent = @mail($cleanEmail, $safeSubject, $html, $headers, "-f " . $cfg['from_email']);
        }

        return [
            'success' => $sent,
            'message' => $sent ? 'Email dispatched successfully.' : 'Email delivery failed on server transport.'
        ];
    }
}

