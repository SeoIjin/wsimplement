<?php
// send_otp.php - Manual PHPMailer Version
// NO HTML OUTPUT ALLOWED - ONLY JSON

ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

ob_start();

// Manual PHPMailer import (adjust path if needed)
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

try {
    // Database configuration
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "users";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        throw new Exception('Database connection failed: ' . $conn->connect_error);
    }

    // Get JSON input
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!$data || !isset($data['email'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Email is required'
        ]);
        exit();
    }

    $email = trim($data['email']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Invalid email format'
        ]);
        exit();
    }

    // Check if email exists
    $stmt = $conn->prepare("SELECT id FROM account WHERE email = ?");
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $stmt->close();
        $conn->close();
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'Email not found in our system'
        ]);
        exit();
    }
    $stmt->close();

    // Generate OTP
    $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

    // Delete old OTPs
    $stmt = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
    if ($stmt) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->close();
    }

    // Insert new OTP
    $stmt = $conn->prepare("INSERT INTO password_resets (email, otp, created_at, expires_at, used) VALUES (?, ?, NOW(), DATE_ADD(NOW(), INTERVAL 60 MINUTE), 0)");
    if (!$stmt) {
        throw new Exception('Failed to generate OTP: ' . $conn->error);
    }

    $stmt->bind_param("ss", $email, $otp);

    if (!$stmt->execute()) {
        throw new Exception('Failed to save OTP: ' . $stmt->error);
    }
    $stmt->close();

    // ============================================
    // SEND EMAIL
    // ============================================
    $mail = new PHPMailer(true);

    try {
        // Gmail SMTP Configuration
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'shouballesteros4@gmail.com';  // YOUR GMAIL HERE
        $mail->Password   = 'ozjfbdrhapodiwuo';          // YOUR APP PASSWORD HERE (remove spaces)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Email settings
        $mail->setFrom('shouballesteros4@gmail.com', 'Barangay 170 BCDRS');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset OTP - Barangay 170';
        
        $mail->Body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <div style='background: linear-gradient(135deg, #a3c3ad 0%, #22594b 100%); padding: 20px; text-align: center;'>
                    <h1 style='color: white; margin: 0;'>Barangay 170 BCDRS</h1>
                    <p style='color: white; margin: 5px 0;'>Password Reset Request</p>
                </div>
                <div style='padding: 30px; background: #f9f9f9;'>
                    <h2 style='color: #22594b;'>Your OTP Code</h2>
                    <p>Hello,</p>
                    <p>You requested to reset your password. Use this OTP code:</p>
                    <div style='background: white; padding: 20px; text-align: center; margin: 20px 0; border-radius: 8px; border: 2px solid #22594b;'>
                        <span style='font-size: 32px; font-weight: bold; color: #22594b; letter-spacing: 8px;'>{$otp}</span>
                    </div>
                    <p><strong>Valid for 60 minutes.</strong></p>
                    <p>If you didn't request this, ignore this email.</p>
                    <hr style='margin: 30px 0; border: none; border-top: 1px solid #ddd;'>
                    <p style='color: #666; font-size: 12px;'>
                        Barangay 170, Deparo, Caloocan City<br>
                        Do not reply to this email.
                    </p>
                </div>
            </div>
        ";
        
        $mail->AltBody = "Your OTP: {$otp}\n\nValid for 60 minutes.\n\nBarangay 170 BCDRS";

        $mail->send();
        $email_status = 'sent';

    } catch (Exception $e) {
        error_log("Email Error: {$mail->ErrorInfo}");
        $email_status = 'failed: ' . $mail->ErrorInfo;
    }

    ob_end_clean();

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'OTP generated and sent to your email',
        'email' => $email,
        'email_status' => $email_status,
        'debug_otp' => $otp // Remove this in production!
    ]);

    $conn->close();

} catch (Exception $e) {
    ob_end_clean();
    error_log('send_otp.php Error: ' . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>