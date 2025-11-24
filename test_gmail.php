<?php
use PHPMailer\PHPMailer\PHPMailer;
require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'shouballesteros4@gmail.com';     // ← Change this
    $mail->Password = 'rioteatfdrulhsec';         // ← Change this (no spaces!)
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;
    
    $mail->setFrom('shouballesteros4@gmail.com');       // ← Change this
    $mail->addAddress('shouballesteros4@gmail.com');    // ← Change this (send to yourself)
    $mail->Subject = 'PHPMailer Test - It Works!';
    $mail->Body = '<h1>Success!</h1><p>Your email is configured correctly!</p>';
    $mail->isHTML(true);
    
    $mail->send();
    echo '✅ SUCCESS! Email sent. Check your inbox!';
} catch (Exception $e) {
    echo '❌ FAILED: ' . $mail->ErrorInfo;
}
?>