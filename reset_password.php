<?php
// reset_password.php - Final Fixed Version
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

    if (!$data || !isset($data['email']) || !isset($data['password']) || !isset($data['reset_token'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Email, password, and reset token are required'
        ]);
        exit();
    }

    $email = trim($data['email']);
    $new_password = $data['password'];
    $reset_token = $data['reset_token'];

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Invalid email format'
        ]);
        exit();
    }

    // Validate password strength
    if (strlen($new_password) < 6) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Password must be at least 6 characters long'
        ]);
        exit();
    }

    // Verify reset token - check if OTP was used
    $stmt = $conn->prepare("
        SELECT id 
        FROM password_resets 
        WHERE email = ? 
        AND used = 1 
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
            'error' => 'Invalid or expired reset token. Please start the password reset process again.'
        ]);
        exit();
    }

    $reset_row = $result->fetch_assoc();
    $reset_id = $reset_row['id'];
    $stmt->close();

    // Store password as plain text (no hashing)
    $password_to_store = $new_password;

    // Update the user's password
    $stmt_update = $conn->prepare("UPDATE account SET password = ? WHERE email = ?");
    if (!$stmt_update) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }

    $stmt_update->bind_param("ss", $password_to_store, $email);
    if (!$stmt_update->execute()) {
        throw new Exception('Execute failed: ' . $stmt_update->error);
    }
    $stmt_update->close();

    // Delete all password reset records for this email
    $stmt_delete = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
    if ($stmt_delete) {
        $stmt_delete->bind_param("s", $email);
        $stmt_delete->execute();
        $stmt_delete->close();
    }

    $conn->close();

    // Clean output buffer
    ob_end_clean();

    // Success response
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Password reset successfully. You can now sign in with your new password.'
    ]);

} catch (Exception $e) {
    // Clean output buffer
    ob_end_clean();
    
    // Log error
    error_log('reset_password.php Error: ' . $e->getMessage());
    
    // Return JSON error
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>