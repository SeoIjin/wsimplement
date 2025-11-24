<?php
// verify_otp.php - Final Fixed Version
// NO HTML OUTPUT ALLOWED - ONLY JSON

ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

ob_start();

try {
    // Database configuration
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "users";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        throw new Exception('Database connection failed: ' . $conn->connect_error);
    }

    // Get JSON input
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!$data || !isset($data['email']) || !isset($data['otp'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Email and OTP are required'
        ]);
        exit();
    }

    $email = trim($data['email']);
    $otp = trim($data['otp']);

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Invalid email format'
        ]);
        exit();
    }

    // Validate OTP format (6 digits)
    if (!preg_match('/^\d{6}$/', $otp)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Invalid OTP format. Must be 6 digits.'
        ]);
        exit();
    }

    // Check if OTP exists and is valid (not used and not expired)
    $stmt = $conn->prepare("
        SELECT id, otp, expires_at, used 
        FROM password_resets 
        WHERE email = ? 
        AND used = 0 
        AND expires_at > NOW()
        ORDER BY created_at DESC 
        LIMIT 1
    ");

    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }

    $stmt->bind_param("s", $email);
    if (!$stmt->execute()) {
        throw new Exception('Execute failed: ' . $stmt->error);
    }

    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $stmt->close();
        $conn->close();
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'No valid OTP found. Please request a new one.'
        ]);
        exit();
    }

    // Get the OTP record
    $row = $result->fetch_assoc();
    $stored_otp = $row['otp'];
    $reset_id = $row['id'];
    $stmt->close();

    // Verify OTP matches
    if ($otp !== $stored_otp) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Invalid OTP. Please check and try again.'
        ]);
        $conn->close();
        exit();
    }

    // OTP is valid - mark it as used
    $stmt_update = $conn->prepare("UPDATE password_resets SET used = 1 WHERE id = ?");
    if (!$stmt_update) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }

    $stmt_update->bind_param("i", $reset_id);
    if (!$stmt_update->execute()) {
        throw new Exception('Execute failed: ' . $stmt_update->error);
    }
    $stmt_update->close();

    // Generate a temporary reset token
    $reset_token = bin2hex(random_bytes(32));

    // Store the reset token
    $stmt_token = $conn->prepare("UPDATE password_resets SET reset_token = ? WHERE id = ?");
    if ($stmt_token) {
        $stmt_token->bind_param("si", $reset_token, $reset_id);
        $stmt_token->execute();
        $stmt_token->close();
    }

    $conn->close();

    // Clean output buffer
    ob_end_clean();

    // Success response
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'OTP verified successfully',
        'reset_token' => $reset_token,
        'email' => $email
    ]);

} catch (Exception $e) {
    // Clean output buffer
    ob_end_clean();
    
    // Log error
    error_log('verify_otp.php Error: ' . $e->getMessage());
    
    // Return JSON error
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>