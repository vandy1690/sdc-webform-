<?php
require_once 'config.php';

// Email sending function using PHP mail()
function sendEmail($to, $subject, $html, $from = null) {
    if (!$from) {
        $from = EMAIL_USER;
    }

    // Headers for HTML email
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: " . ADMIN_NAME . " <" . $from . ">" . "\r\n";
    $headers .= "Reply-To: " . $from . "\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();

    return mail($to, $subject, $html, $headers);
}

// Alternative SMTP function using PHPMailer (if available)
function sendEmailSMTP($to, $subject, $html, $from = null) {
    // Check if PHPMailer is available
    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        return sendEmail($to, $subject, $html, $from);
    }

    require_once 'vendor/autoload.php';

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = EMAIL_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = EMAIL_USER;
        $mail->Password = EMAIL_PASS;
        $mail->SMTPSecure = EMAIL_SECURE ? PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS : PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = EMAIL_PORT;

        // Recipients
        $mail->setFrom($from ?: EMAIL_USER, ADMIN_NAME);
        $mail->addAddress($to);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $html;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email sending failed: " . $mail->ErrorInfo);
        return false;
    }
}

// Client email template
function getClientEmailTemplate($data) {
    $date = date('F j, Y');

    return "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='utf-8'>
        <title>Bid Request Confirmation</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #2563eb; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background: #f9fafb; }
            .footer { padding: 20px; text-align: center; color: #666; font-size: 14px; }
            .highlight { background: #dbeafe; padding: 15px; border-radius: 5px; margin: 15px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Thank You for Your Bid Request!</h1>
            </div>
            <div class='content'>
                <p>Dear {$data['firstName']} {$data['lastName']},</p>
                <p>Thank you for reaching out to SDC Creative Studio! We've received your bid request and are excited to learn more about your project.</p>

                <div class='highlight'>
                    <h3>Project Details:</h3>
                    <p><strong>Project:</strong> {$data['projectTitle']}</p>
                    <p><strong>Type:</strong> " . str_replace('-', ' ', strtoupper($data['projectType'])) . "</p>
                    <p><strong>Budget Range:</strong> " . str_replace('-', ' - ', strtoupper($data['budget'])) . "</p>
                    <p><strong>Timeline:</strong> " . str_replace('-', ' ', strtoupper($data['timeline'])) . "</p>
                </div>

                <p>Our team will review your project details and get back to you within 24 hours with a detailed quote and next steps.</p>

                <p>If you have any questions in the meantime, please don't hesitate to reach out to us.</p>

                <p>Best regards,<br>
                The SDC Creative Studio Team</p>
            </div>
            <div class='footer'>
                <p>This email was sent in response to your bid request submitted on {$date}.</p>
            </div>
        </div>
    </body>
    </html>
    ";
}

// Admin email template
function getAdminEmailTemplate($data) {
    $services = is_array($data['services']) ? implode(', ', $data['services']) : ($data['services'] ?: 'None specified');

    return "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='utf-8'>
        <title>New Bid Request - {$data['projectTitle']}</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #dc2626; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background: #f9fafb; }
            .section { margin: 20px 0; padding: 15px; background: white; border-radius: 5px; }
            .highlight { background: #fef3c7; padding: 10px; border-radius: 5px; margin: 10px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>New Bid Request Received!</h1>
            </div>
            <div class='content'>
                <div class='section'>
                    <h3>Contact Information</h3>
                    <p><strong>Name:</strong> {$data['firstName']} {$data['lastName']}</p>
                    <p><strong>Email:</strong> {$data['email']}</p>
                    <p><strong>Phone:</strong> " . (isset($data['phone']) && $data['phone'] ? $data['phone'] : 'Not provided') . "</p>
                    <p><strong>Company:</strong> " . (isset($data['company']) && $data['company'] ? $data['company'] : 'Not provided') . "</p>
                </div>

                <div class='section'>
                    <h3>Project Details</h3>
                    <p><strong>Project Title:</strong> {$data['projectTitle']}</p>
                    <p><strong>Project Type:</strong> " . str_replace('-', ' ', strtoupper($data['projectType'])) . "</p>
                    <p><strong>Budget Range:</strong> " . str_replace('-', ' - ', strtoupper($data['budget'])) . "</p>
                    <p><strong>Timeline:</strong> " . str_replace('-', ' ', strtoupper($data['timeline'])) . "</p>
                    <p><strong>Services Needed:</strong> {$services}</p>
                    <p><strong>How they heard about us:</strong> " . (isset($data['referral']) && $data['referral'] ? $data['referral'] : 'Not specified') . "</p>
                </div>

                <div class='section'>
                    <h3>Project Description</h3>
                    <p>{$data['description']}</p>
                </div>

                <div class='highlight'>
                    <p><strong>Action Required:</strong> Please review this bid request and respond within 24 hours.</p>
                    <p><strong>Bid ID:</strong> #{$data['id']}</p>
                </div>
            </div>
        </div>
    </body>
    </html>
    ";
}

// Send emails for bid request
function sendBidRequestEmails($data) {
    $clientSuccess = false;
    $adminSuccess = false;

    // Send confirmation email to client
    $clientEmail = getClientEmailTemplate($data);
    $clientSuccess = sendEmail(
        $data['email'],
        'Thank you for your bid request - SDC Creative Studio',
        $clientEmail
    );

    // Send notification email to admin
    $adminEmail = getAdminEmailTemplate($data);
    $adminSuccess = sendEmail(
        ADMIN_EMAIL,
        'New Bid Request: ' . $data['projectTitle'],
        $adminEmail
    );

    return [
        'client' => $clientSuccess,
        'admin' => $adminSuccess
    ];
}
?>