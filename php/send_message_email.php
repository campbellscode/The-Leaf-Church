<?php
error_reporting(E_ALL);
ini_set('display_errors', 1); // set to 0 in production

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

header('Content-Type: application/json');

$response = ['success' => false, 'errors' => [], 'message' => ''];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name    = trim(strip_tags($_POST["name"] ?? ''));
    $name    = str_replace(["\r", "\n"], ' ', $name);
    $email   = filter_var(trim($_POST["email"] ?? ''), FILTER_SANITIZE_EMAIL);
    $message = trim($_POST["message"] ?? '');

    // NEW: referral dropdown
    $referral = trim(strip_tags($_POST["referral"] ?? ''));

    // Allowed values (keep in sync with the <option value="..."> list)
    $allowed_referrals = [
        'Facebook',
        'Instagram',
        'YouTube',
        'TikTok',
        'Google'
    ];

    // Basic validation
    if ($name === '') {
        $response['errors']['name'] = "Please provide your name.";
    }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['errors']['email'] = "Please provide a valid email address.";
    }
    if ($message === '') {
        $response['errors']['message'] = "Please provide a message.";
    } elseif (strlen($message) > 1000) {
        $response['errors']['message'] = "The message is too long. Please keep it under 1000 characters.";
    }

    // NEW: validate referral
    if ($referral === '' || !in_array($referral, $allowed_referrals, true)) {
        $response['errors']['referral'] = "Please tell us how you heard about us.";
    }

    if (empty($response['errors'])) {
        $mail = new PHPMailer(true);
        try {
            // GoDaddy relay (unauthenticated). Consider switching to authenticated SMTP 587/TLS for better deliverability.
            $mail->SMTPDebug   = 0;
            $mail->isSMTP();
            $mail->Host        = 'dedrelay.secureserver.net';
            $mail->SMTPAuth    = false;
            $mail->SMTPSecure  = false;
            $mail->SMTPAutoTLS = false;
            $mail->Port        = 25;

            $mail->setFrom('contact-form@theleafchurchcincy.org', 'The Leaf Church');
            $mail->addAddress('theleafchurch@gmail.com');
            $mail->addAddress('leelantus@proton.me');
            if ($email) {
                $mail->addReplyTo($email, $name);
            }

            $mail->isHTML(false);
            $mail->Subject = 'Contact Message from ' . $name;

            // NEW: include referral in email body
            $mail->Body =
                "Name: $name\n" .
                "Email: $email\n\n" .
                "Message:\n$message\n\n" .
                "How did you hear about us? $referral\n";

            $mail->send();
            $response['success'] = true;
            $response['message'] = 'Your message has been sent.';
        } catch (Exception $e) {
            $response['message'] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }
}

echo json_encode($response);
exit;
