<?php

class Email {

    private static $config;

    private static function getConfig() {
        if (!self::$config) {
            self::$config = require __DIR__ . '/../config/email.php';
        }
        return self::$config;
    }

    private static function log($message) {
        $logFile = __DIR__ . '/../../email.log';
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
        // Also log to PHP error log
        error_log($message);
    }

    public static function send($to, $subject, $body, $isHtml = true) {
        $config = self::getConfig();

        $headers = [];
        $headers[] = 'From: ' . self::formatEmail($config['from_email'], $config['from_name']);
        $headers[] = 'Reply-To: ' . $config['from_email'];
        $headers[] = 'X-Mailer: PHP/' . phpversion();

        if ($isHtml) {
            $headers[] = 'MIME-Version: 1.0';
            $headers[] = 'Content-type: text/html; charset=utf-8';
        }

        // Use SMTP if we need authentication
        if ($config['smtp_username'] && $config['smtp_password']) {
            return self::sendSmtp($to, $subject, $body, $headers);
        } else {
            // Fallback to PHP mail()
            return mail($to, $subject, $body, implode("\r\n", $headers));
        }
    }

    private static function sendSmtp($to, $subject, $body, $headers) {
        $config = self::getConfig();

        try {
            self::log("SMTP: Attempting connection to {$config['smtp_server']}:{$config['smtp_port']}");
            $socket = fsockopen($config['smtp_server'], $config['smtp_port'], $errno, $errstr, 30);

            if (!$socket) {
                self::log("SMTP connection failed: $errstr ($errno)");
                throw new Exception("Connection failed: $errstr ($errno)");
            }

            // Read server greeting
            $response = fgets($socket, 515);
            self::log("SMTP greeting: " . trim($response));

            // Send EHLO
            $serverName = $_SERVER['SERVER_NAME'] ?? 'localhost';
            fputs($socket, "EHLO $serverName\r\n");

            // Read all EHLO response lines (multi-line response)
            $response = '';
            do {
                $line = fgets($socket, 515);
                $response .= $line;
                self::log("SMTP EHLO response: " . trim($line));
            } while (isset($line[3]) && $line[3] === '-'); // Continue while response has continuation indicator

            // STARTTLS if enabled
            if ($config['use_starttls']) {
                fputs($socket, "STARTTLS\r\n");
                $response = fgets($socket, 515);
                self::log("SMTP STARTTLS response: " . trim($response));

                if (strpos($response, '220') !== 0) {
                    self::log("STARTTLS failed: $response");
                    fclose($socket);
                    throw new Exception("STARTTLS failed: $response");
                }

                $cryptoResult = stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
                if (!$cryptoResult) {
                    self::log("SMTP: TLS encryption failed");
                    fclose($socket);
                    throw new Exception("TLS encryption failed");
                }
                self::log("SMTP: TLS encryption enabled");

                // Send EHLO again after STARTTLS
                fputs($socket, "EHLO $serverName\r\n");

                // Read all EHLO response lines (multi-line response)
                $response = '';
                do {
                    $line = fgets($socket, 515);
                    $response .= $line;
                    self::log("SMTP EHLO after TLS: " . trim($line));
                } while (isset($line[3]) && $line[3] === '-'); // Continue while response has continuation indicator
            }

            // AUTH LOGIN
            fputs($socket, "AUTH LOGIN\r\n");
            $response = fgets($socket, 515);
            self::log("SMTP AUTH LOGIN response: " . trim($response));

            fputs($socket, base64_encode($config['smtp_username']) . "\r\n");
            $response = fgets($socket, 515);
            self::log("SMTP username response: " . trim($response));

            fputs($socket, base64_encode($config['smtp_password']) . "\r\n");
            $response = fgets($socket, 515);
            self::log("SMTP password response: " . trim($response));

            if (strpos($response, '235') !== 0) {
                self::log("SMTP authentication failed: $response");
                fclose($socket);
                throw new Exception("Authentication failed: $response");
            }
            self::log("SMTP: Authentication successful");

            // MAIL FROM
            fputs($socket, "MAIL FROM: <" . $config['from_email'] . ">\r\n");
            $response = fgets($socket, 515);
            self::log("SMTP MAIL FROM response: " . trim($response));

            // RCPT TO
            fputs($socket, "RCPT TO: <$to>\r\n");
            $response = fgets($socket, 515);
            self::log("SMTP RCPT TO response: " . trim($response));

            // DATA
            fputs($socket, "DATA\r\n");
            $response = fgets($socket, 515);
            self::log("SMTP DATA response: " . trim($response));

            // Send headers and body
            fputs($socket, implode("\r\n", $headers) . "\r\n");
            fputs($socket, "To: $to\r\n");
            fputs($socket, "Subject: $subject\r\n");
            fputs($socket, "\r\n");
            fputs($socket, $body . "\r\n");
            fputs($socket, ".\r\n");
            $response = fgets($socket, 515);
            self::log("SMTP final response: " . trim($response));

            // QUIT
            fputs($socket, "QUIT\r\n");
            fclose($socket);

            $success = strpos($response, '250') === 0;
            self::log("SMTP: Email " . ($success ? "sent successfully" : "failed to send"));

            // Save to IMAP Sent folder if enabled and email was sent successfully
            if ($success && !empty($config['save_to_sent']) && $config['save_to_sent']) {
                self::saveToSentFolder($to, $subject, $body, $headers);
            }

            return $success;

        } catch (Exception $e) {
            self::log("SMTP error: " . $e->getMessage());
            return false;
        }
    }

    private static function saveToSentFolder($to, $subject, $body, $headers) {
        $config = self::getConfig();

        try {
            self::log("IMAP: Attempting to save email to Sent folder");

            // Build IMAP connection string
            $imapServer = $config['imap_use_ssl']
                ? '{' . $config['imap_server'] . ':' . $config['imap_port'] . '/imap/ssl}'
                : '{' . $config['imap_server'] . ':' . $config['imap_port'] . '/imap}';

            // Connect to IMAP
            $inbox = @imap_open(
                $imapServer . 'INBOX',
                $config['smtp_username'],
                $config['smtp_password']
            );

            if (!$inbox) {
                self::log("IMAP: Connection failed - " . imap_last_error());
                return false;
            }

            self::log("IMAP: Connected successfully");

            // Build the email message
            $message = implode("\r\n", $headers) . "\r\n";
            $message .= "To: $to\r\n";
            $message .= "Subject: $subject\r\n";
            $message .= "Date: " . date('r') . "\r\n";
            $message .= "\r\n";
            $message .= $body;

            // Append to Sent folder
            $sentFolder = $imapServer . $config['imap_sent_folder'];
            $result = @imap_append($inbox, $sentFolder, $message, "\\Seen");

            if ($result) {
                self::log("IMAP: Email saved to Sent folder successfully");
            } else {
                self::log("IMAP: Failed to save to Sent folder - " . imap_last_error());
            }

            imap_close($inbox);
            return $result;

        } catch (Exception $e) {
            self::log("IMAP error: " . $e->getMessage());
            return false;
        }
    }

    private static function formatEmail($email, $name = null) {
        if ($name) {
            // Encode the name properly for email headers (RFC 2047)
            $encodedName = '=?UTF-8?B?' . base64_encode($name) . '?=';
            return "$encodedName <$email>";
        }
        return $email;
    }

    public static function sendPasswordSetup($email, $fullName, $token) {
        $config = self::getConfig();

        $setupUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http")
                  . "://" . $_SERVER['HTTP_HOST'] . "/setup-password?token=" . urlencode($token);

        $subject = $config['subject_registration'];

        $body = self::getPasswordSetupEmailTemplate($fullName, $setupUrl);

        return self::send($email, $subject, $body);
    }

    private static function getPasswordSetupEmailTemplate($fullName, $setupUrl) {
        $name = $fullName ?: 'Uživateli';

        return "
<!DOCTYPE html>
<html lang='cs'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Registrace do VetApp</title>
</head>
<body style='margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, \"Helvetica Neue\", Arial, sans-serif; background-color: #f4f4f4;'>
    <table width='100%' cellpadding='0' cellspacing='0' style='background-color: #f4f4f4; padding: 20px 0;'>
        <tr>
            <td align='center'>
                <table width='600' cellpadding='0' cellspacing='0' style='max-width: 600px; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);'>
                    <!-- Header -->
                    <tr>
                        <td style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 40px 30px; text-align: center;'>
                            <h1 style='margin: 0; color: #ffffff; font-size: 28px; font-weight: 600;'>VetApp ZOO Tábor</h1>
                            <p style='margin: 8px 0 0 0; color: #f0f0f0; font-size: 14px;'>Veterinární aplikace</p>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style='padding: 40px 30px;'>
                            <h2 style='margin: 0 0 20px 0; color: #2c3e50; font-size: 22px; font-weight: 600;'>Vítejte v systému!</h2>

                            <p style='margin: 0 0 15px 0; color: #555; line-height: 1.6;'>Dobrý den <strong>$name</strong>,</p>

                            <p style='margin: 0 0 15px 0; color: #555; line-height: 1.6;'>Byl pro vás vytvořen účet v aplikaci VetApp ZOO Tábor pro správu veterinární evidence.</p>

                            <p style='margin: 0 0 25px 0; color: #555; line-height: 1.6;'>Pro dokončení registrace a nastavení hesla klikněte na tlačítko níže:</p>

                            <!-- Button -->
                            <table width='100%' cellpadding='0' cellspacing='0'>
                                <tr>
                                    <td align='center' style='padding: 10px 0 30px 0;'>
                                        <a href='$setupUrl' style='display: inline-block; padding: 16px 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #ffffff; text-decoration: none; border-radius: 50px; font-weight: 600; font-size: 16px; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);'>
                                            🔐 Nastavit heslo
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style='margin: 0 0 10px 0; color: #555; font-size: 14px;'>Nebo zkopírujte tento odkaz do prohlížeče:</p>
                            <p style='margin: 0 0 25px 0; padding: 12px; background-color: #f8f9fa; border: 1px solid #e9ecef; border-radius: 6px; word-break: break-all; font-size: 13px;'>
                                <a href='$setupUrl' style='color: #667eea; text-decoration: none;'>$setupUrl</a>
                            </p>

                            <!-- Security Notice -->
                            <table width='100%' cellpadding='0' cellspacing='0' style='margin-top: 30px; border-top: 2px solid #e9ecef; padding-top: 20px;'>
                                <tr>
                                    <td style='padding: 15px; background-color: #fff3cd; border-left: 4px solid #ffc107; border-radius: 4px;'>
                                        <p style='margin: 0 0 8px 0; color: #856404; font-weight: 600; font-size: 14px;'>⚠️ Bezpečnostní upozornění</p>
                                        <p style='margin: 0; color: #856404; font-size: 13px; line-height: 1.5;'>
                                            Tento odkaz je platný 48 hodin. Pokud nevytvoříte heslo do této doby, budete muset požádat administrátora o vytvoření nového odkazu.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <p style='margin: 20px 0 0 0; color: #999; font-size: 13px; line-height: 1.5;'>
                                Pokud jste o registraci nežádali, můžete tento e-mail ignorovat.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style='background-color: #f8f9fa; padding: 25px 30px; text-align: center; border-top: 1px solid #e9ecef;'>
                            <p style='margin: 0 0 5px 0; color: #6c757d; font-size: 14px; font-weight: 500;'>ZOO Tábor</p>
                            <p style='margin: 0 0 10px 0; color: #999; font-size: 12px;'>VetApp ZOO Tábor</p>
                            <p style='margin: 0; color: #adb5bd; font-size: 11px;'>&copy; " . date('Y') . " Všechna práva vyhrazena</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
        ";
    }
}
