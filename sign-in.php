<?php
session_start();

$servername = "localhost";
$username = "root";  
$password = "";      
$dbname = "users";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_SESSION['user_id'])) {
    header("Location: homepage.php");
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    // Prepare and execute query
    $stmt = $conn->prepare("SELECT id, password, usertype, first_name, last_name FROM account WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id, $stored_password, $usertype, $first_name, $last_name);
    $stmt->fetch();
    
    if ($stmt->num_rows > 0) {
        // Check if password is hashed or plain text
        if (password_verify($password, $stored_password)) {
            // Password is hashed and correct
            $password_correct = true;
        } elseif ($password === $stored_password) {
            // Password is plain text and correct (for backward compatibility)
            $password_correct = true;
        } else {
            $password_correct = false;
        }
        
        if ($password_correct) {
            $_SESSION['user_id'] = $id;
            $_SESSION['email'] = $email;
            $_SESSION['user_name'] = $first_name . ' ' . $last_name;
            
            // Check if admin based on email domain or usertype
            $email_domain = '';
            if (strpos($email, '@') !== false) {
                $parts = explode('@', $email);
                $email_domain = strtolower(array_pop($parts));
            }

            if ($email_domain === 'gov.ph' || $usertype === 'admin') {
                $_SESSION['is_admin'] = true;
                header("Location: admindashboard.php");
                exit();
            } else {
                $_SESSION['is_admin'] = false;
                header("Location: homepage.php");
                exit();
            }
        } else {
            $error = "Invalid email or password.";
        }
    } else {
        $error = "Invalid email or password.";
    }
    $stmt->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>eBCsH System - Sign In</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&family=Quicksand:wght@500&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            font-family: 'Poppins', 'Quicksand', Arial, sans-serif;
        }
        
        .container {
            display: flex;
            width: 100vw;
            height: 100vh;
            overflow: hidden;
        }
        
        /* Left Panel - Merged Design */
        .left-panel {
            flex: 1;
            min-width: 320px;
            background: linear-gradient(135deg, #a3c3ad 0%, #22594b 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px;
        }
        
        .logo-img {
            width: 270px;
            max-width: 55%;
            border-radius: 130px;
            margin-bottom: 30px;
        }
        
        .welcome-text {
            color: #fff;
            font-size: 1.25rem;
            text-align: center;
            margin-top: 12px;
            line-height: 1.4;
        }
        
        /* Right Panel - Merged Design */
        .right-panel {
            flex: 1;
            min-width: 420px;
            background: #fff;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 56px;
        }
        
        /* Tab Group */
        .tab-group {
            display: flex;
            width: 100%;
            max-width: 760px;
            background: transparent;
            border-radius: 0;
            margin-bottom: 28px;
            overflow: visible;
            height: auto;
            align-items: center;
            gap: 16px;
            justify-content: center;
        }
        
        .tab-btn {
            flex: 0 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            background: transparent;
            border: none;
            font-size: 1.05rem;
            color: #999;
            cursor: pointer;
            font-weight: 500;
            border-radius: 12px;
            padding: 12px 40px;
            transition: all 0.3s ease;
            min-width: 140px;
        }
        
        .tab-btn.active {
            background: #fff;
            color: #22594b;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        /* Form Container */
        .form-container {
            width: 100%;
            max-width: 760px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .form-header {
            width: 100%;
            text-align: left;
            margin-bottom: 8px;
        }
        
        .form-title {
            font-family: 'Montserrat', 'Poppins', Arial, sans-serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: #222;
            margin-bottom: 8px;
        }
        
        .form-subtitle {
            font-size: 1.05rem;
            color: #888;
            margin-bottom: 20px;
        }
        
        .form-content {
            width: 100%;
        }
        
        /* Error Message */
        .error {
            color: #dc2626;
            font-size: 0.875rem;
            margin-bottom: 12px;
            padding: 8px 12px;
            background: #fee2e2;
            border-radius: 6px;
        }
        
        /* Input Groups */
        .input-group {
            margin-bottom: 20px;
            width: 100%;
        }
        
        .input-label {
            font-size: 1rem;
            color: #222;
            margin-bottom: 8px;
            font-weight: 600;
            display: block;
        }
        
        .input-eye-wrapper {
            position: relative;
            width: 100%;
        }
        
        .input-box {
            width: 100%;
            padding: 14px 16px 14px 48px;
            border-radius: 10px;
            border: 1px solid #e5e7eb;
            background: #fff;
            font-size: 1rem;
            color: #222;
            min-height: 48px;
            outline: none;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
        }
        
        .input-box:focus {
            border-color: #22594b;
            box-shadow: 0 0 0 3px rgba(34, 89, 75, 0.1);
        }
        
        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            width: 20px;
            height: 20px;
            opacity: 0.6;
            pointer-events: none;
        }
        
        .eye-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            width: 20px;
            height: 20px;
            cursor: pointer;
            opacity: 0.6;
            background: transparent;
            border: 0;
            padding: 0;
        }
        
        .eye-toggle img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        
        /* Options Row */
        .options-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 12px 0 24px 0;
            width: 100%;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .checkbox-group input[type="checkbox"] {
            accent-color: #22594b;
            cursor: pointer;
        }
        
        .checkbox-group label {
            cursor: pointer;
        }
        
        .forgot-link {
            color: #22594b;
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
            background: transparent;
            border: none;
            font-size: 1rem;
            font-family: 'Poppins', sans-serif;
        }
        
        .forgot-link:hover {
            text-decoration: underline;
        }
        
        /* Submit Button */
        .submit-btn {
            width: 100%;
            padding: 14px 0;
            border-radius: 10px;
            border: none;
            background: #22594b;
            color: #fff;
            font-size: 1.05rem;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(34, 89, 75, 0.2);
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
        }
        
        .submit-btn:hover {
            background: #1a453a;
            box-shadow: 0 4px 12px rgba(34, 89, 75, 0.3);
        }
        
        /* Bottom Text */
        .bottom-text {
            width: 100%;
            text-align: center;
            margin-top: 20px;
            color: #666;
            font-size: 1rem;
        }
        
        .bottom-text a {
            color: #22594b;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            background: transparent;
            border: none;
            font-size: 1rem;
            font-family: 'Poppins', sans-serif;
        }
        
        .bottom-text a:hover {
            text-decoration: underline;
        }
        
        /* Divider */
        .divider {
            display: flex;
            align-items: center;
            gap: 16px;
            margin: 24px 0;
            width: 100%;
        }
        
        .divider-line {
            flex: 1;
            height: 1px;
            background: #e5e7eb;
        }
        
        .divider-text {
            color: #888;
            font-size: 0.875rem;
        }
        
        /* Guest Portal Button */
        .guest-btn {
            width: 100%;
            padding: 14px 0;
            border-radius: 10px;
            border: 2px solid #3b82f6;
            background: transparent;
            color: #3b82f6;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
        }
        
        .guest-btn:hover {
            background: rgba(59, 130, 246, 0.05);
        }
        
        .guest-btn-icon {
            width: 20px;
            height: 20px;
            background: #3b82f6;
            color: white;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.875rem;
            font-weight: bold;
        }
        
        .guest-btn-subtitle {
            text-align: center;
            color: #888;
            font-size: 0.875rem;
            margin-top: 12px;
        }
        
        /* Forgot Password Modal */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 1000;
            background: linear-gradient(135deg, #d1fae5 0%, #ecfdf5 50%, #ccfbf1 100%);
            flex-direction: column;
        }
        
        .modal-overlay.show {
            display: flex;
        }
        
        .modal-header {
            width: 100%;
            background: #fff;
            border-bottom: 1px solid #e5e7eb;
            padding: 12px 24px;
        }
        
        .modal-header-content {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .modal-logo {
            width: 40px;
            height: 40px;
        }
        
        .modal-header-text h1 {
            font-size: 1.125rem;
            font-weight: 600;
            color: #166534;
            margin: 0;
        }
        
        .modal-header-text p {
            font-size: 0.875rem;
            color: #16a34a;
            margin: 0;
        }
        
        .modal-body {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
            overflow-y: auto;
        }
        
        .modal-content {
            background: #fff;
            border-radius: 16px;
            padding: 32px;
            max-width: 448px;
            width: 100%;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        .modal-content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }
        
        .modal-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #22594b;
            margin: 0;
        }
        
        .modal-close {
            background: transparent;
            border: none;
            color: #6b7280;
            font-size: 2rem;
            line-height: 1;
            cursor: pointer;
            padding: 0;
        }
        
        .modal-close:hover {
            color: #374151;
        }
        
        .modal-alert {
            padding: 12px;
            border-radius: 8px;
            font-size: 0.875rem;
            margin-bottom: 16px;
        }
        
        .modal-alert.error {
            background: #fee2e2;
            border: 1px solid #fca5a5;
            color: #991b1b;
        }
        
        .modal-alert.success {
            background: #d1fae5;
            border: 1px solid #86efac;
            color: #166534;
        }
        
        .modal-description {
            color: #4b5563;
            margin-bottom: 16px;
            line-height: 1.5;
        }
        
        .modal-input-group {
            margin-bottom: 20px;
        }
        
        .modal-input-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #374151;
        }
        
        .modal-input-wrapper {
            position: relative;
        }
        
        .modal-input {
            width: 100%;
            padding: 12px 16px;
            border-radius: 8px;
            border: none;
            background: #f5f6fa;
            font-size: 1rem;
            outline: none;
            font-family: 'Poppins', sans-serif;
        }
        
        .modal-input.with-icon {
            padding-left: 48px;
        }
        
        .modal-input.otp-input {
            text-align: center;
            font-size: 1.5rem;
            letter-spacing: 0.25em;
            font-family: monospace;
        }
        
        .modal-input:focus {
            box-shadow: 0 0 0 2px #a3c3ad;
        }
        
        .modal-input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            width: 20px;
            height: 20px;
            opacity: 0.7;
            pointer-events: none;
        }
        
        .modal-btn {
            width: 100%;
            padding: 12px 16px;
            border-radius: 8px;
            border: none;
            background: linear-gradient(180deg, #163832, #194f43);
            color: #fff;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: opacity 0.3s ease;
            font-family: 'Poppins', sans-serif;
        }
        
        .modal-btn:hover {
            opacity: 0.9;
        }
        
        .modal-btn.secondary {
            background: transparent;
            color: #22594b;
            font-weight: 500;
            margin-top: 12px;
        }
        
        .modal-btn.secondary:hover {
            text-decoration: underline;
        }
        
        .modal-footer {
            width: 100%;
            background: #fff;
            border-top: 1px solid #dcfce7;
            padding: 24px;
        }
        
        .footer-content {
            max-width: 1000px;
            margin: 0 auto;
        }
        
        .footer-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 32px;
            margin-bottom: 16px;
        }
        
        .footer-section {
            text-align: center;
        }
        
        .footer-section h3 {
            font-size: 1.125rem;
            color: #14532d;
            font-weight: 600;
            margin-bottom: 16px;
        }
        
        .footer-section-content {
            display: inline-block;
            text-align: left;
        }
        
        .footer-item {
            margin-bottom: 12px;
            font-size: 0.9375rem;
        }
        
        .footer-item-label {
            color: #15803d;
            font-weight: 500;
            margin-bottom: 4px;
        }
        
        .footer-item-value {
            color: #166534;
            font-size: 0.875rem;
        }
        
        .footer-copyright {
            border-top: 1px solid #dcfce7;
            padding-top: 16px;
            text-align: center;
            color: #15803d;
            font-size: 0.9375rem;
        }
        
        .footer-copyright p {
            margin: 4px 0;
        }
        
        /* Responsive Design */
        @media (max-width: 980px) {
            .container {
                flex-direction: column;
                height: auto;
            }
            
            .left-panel, .right-panel {
                width: 100%;
                padding: 32px 20px;
                min-width: auto;
            }
            
            .logo-img {
                max-width: 220px;
                width: 220px;
                margin-bottom: 20px;
            }
            
            .tab-group, .form-container {
                max-width: 100%;
            }
            
            .footer-grid {
                grid-template-columns: 1fr;
                gap: 24px;
            }
        }
        
        @media (max-width: 640px) {
            .right-panel {
                padding: 24px 16px;
            }
            
            .modal-content {
                padding: 24px;
            }
            
            .form-title {
                font-size: 1.25rem;
            }
        }
    </style>
</head>
<body>
    <!-- Forgot Password Modal -->
    <div id="forgotModal" class="modal-overlay">
        <div class="modal-header">
            <div class="modal-header-content">
                <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRTDCuh4kIpAtR-QmjA1kTjE_8-HSd8LSt3Gw&s" alt="Barangay Logo" class="modal-logo">
                <div class="modal-header-text">
                    <h1>Barangay 170</h1>
                    <p>Deparo, Caloocan</p>
                </div>
            </div>
        </div>
        <div class="modal-body" onclick="closeForgotModal(event)">
            <div class="modal-content" onclick="event.stopPropagation()">
                <div class="modal-content-header">
                    <h2 id="modalTitle" class="modal-title">Forgot Password</h2>
                    <button class="modal-close" onclick="closeForgotModal()">&times;</button>
                </div>
                <div id="modalError" class="modal-alert error" style="display: none;"></div>
                <div id="modalSuccess" class="modal-alert success" style="display: none;"></div>
                <!-- Step 1: Email -->
                <div id="stepEmail">
                    <p class="modal-description">Enter your email address and we'll send you an OTP to reset your password.</p>
                    <form onsubmit="handleSendOTP(event)">
                        <div class="modal-input-group">
                            <label class="modal-input-label">Email Address</label>
                            <div class="modal-input-wrapper">
                                <img src="https://img.icons8.com/ios-filled/50/000000/new-post.png" alt="" class="modal-input-icon">
                                <input type="email" id="forgotEmail" class="modal-input with-icon" placeholder="Enter your email" required>
                            </div>
                        </div>
                        <button type="submit" class="modal-btn">Send OTP</button>
                    </form>
                </div>
                <!-- Step 2: OTP -->
                <div id="stepOTP" style="display: none;">
                    <p class="modal-description">We've sent a 6-digit OTP to <strong id="emailDisplay"></strong></p>
                    <form onsubmit="handleVerifyOTP(event)">
                        <div class="modal-input-group">
                            <label class="modal-input-label">Enter OTP</label>
                            <input type="text" id="otpInput" class="modal-input otp-input" placeholder="000000" maxlength="6" required>
                        </div>
                        <button type="submit" class="modal-btn">Verify OTP</button>
                        <button type="button" class="modal-btn secondary" onclick="resendOTP()">Resend OTP</button>
                    </form>
                </div>
                <!-- Step 3: New Password -->
                <div id="stepNewPass" style="display: none;">
                    <p class="modal-description">Create a new password for your account.</p>
                    <form onsubmit="handleResetPassword(event)">
                        <div class="modal-input-group">
                            <label class="modal-input-label">New Password</label>
                            <div class="modal-input-wrapper">
                                <img src="https://cdn-icons-png.flaticon.com/128/345/345535.png" alt="" class="modal-input-icon">
                                <input type="password" id="newPassword" class="modal-input with-icon" placeholder="Enter new password" required>
                            </div>
                        </div>
                        <div class="modal-input-group">
                            <label class="modal-input-label">Confirm New Password</label>
                            <div class="modal-input-wrapper">
                                <img src="https://cdn-icons-png.flaticon.com/128/345/345535.png" alt="" class="modal-input-icon">
                                <input type="password" id="confirmPassword" class="modal-input with-icon" placeholder="Confirm new password" required>
                            </div>
                        </div>
                        <button type="submit" class="modal-btn">Reset Password</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <div class="footer-content">
                <div class="footer-grid">
                    <div class="footer-section">
                        <h3>🏢 Barangay Health Office</h3>
                        <div class="footer-section-content">
                            <div class="footer-item">
                                <div class="footer-item-label">📍 Address</div>
                                <div class="footer-item-value">Deparo, Caloocan City, Metro Manila</div>
                            </div>
                            <div class="footer-item">
                                <div class="footer-item-label">📞 Hotline</div>
                                <div class="footer-item-value">(02) 8123-4567</div>
                            </div>
                            <div class="footer-item">
                                <div class="footer-item-label">📧 Email</div>
                                <div class="footer-item-value">K1contrerascris@gmail.com</div>
                            </div>
                            <div class="footer-item">
                                <div class="footer-item-label">🕐 Office Hours</div>
                                <div class="footer-item-value">Mon-Fri, 8:00 AM - 5:00 PM</div>
                            </div>
                        </div>
                    </div>
                    <div class="footer-section">
                        <h3>📞 Emergency Hotlines</h3>
                        <div class="footer-section-content">
                            <div class="footer-item">
                                <span class="footer-item-label" style="display: inline-block; min-width: 80px;">Police</span>
                                <span class="footer-item-value">(02) 8426-4663</span>
                            </div>
                            <div class="footer-item">
                                <span class="footer-item-label" style="display: inline-block; min-width: 80px;">BFP</span>
                                <span class="footer-item-value">(02) 8245 0849</span>
                            </div>
                        </div>
                    </div>
                    <div class="footer-section">
                        <h3>🏥 Hospitals Near Barangay</h3>
                        <div class="footer-section-content">
                            <div class="footer-item">
                                <div class="footer-item-label">Camarin Doctors Hospital</div>
                                <div class="footer-item-value">2-7004-2881</div>
                            </div>
                            <div class="footer-item">
                                <div class="footer-item-label">Caloocan City North Medical</div>
                                <div class="footer-item-value">(02) 8288 7077</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="footer-copyright">
                    <p>© 2025 Barangay 170, Deparo, Caloocan. All rights reserved.</p>
                    <p>Barangay Citizen Document Request System (BCDRS)</p>
                </div>
            </div>
        </div>
    </div>
    <!-- Main Sign In Page -->
    <div class="container">
        <div class="left-panel">
            <img class="logo-img" src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRTDCuh4kIpAtR-QmjA1kTjE_8-HSd8LSt3Gw&s" alt="Logo">
            <div class="welcome-text">
                Welcome to BCDRS<br>Your friendly assistant is here to help!
            </div>
        </div>
        <div class="right-panel">
            <div class="tab-group">
                <button class="tab-btn active" type="button" onclick="location.href='sign-in.php'">Sign In</button>
                <button class="tab-btn" type="button" onclick="location.href='sign-up.php'">Sign Up</button>
            </div>
            <div class="form-container">
                <div class="form-header">
                    <h2 class="form-title">Welcome Back!</h2>
                    <p class="form-subtitle">Sign in to access your account</p>
                </div>
                <div class="form-content">
                    <?php if ($error): ?>
                        <div class="error"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <form method="POST" action="sign-in.php">
                        <div class="input-group">
                            <label class="input-label">Email Address</label>
                            <div class="input-eye-wrapper">
                                <img class="input-icon" src="https://img.icons8.com/ios-filled/50/000000/new-post.png" alt="">
                                <input class="input-box" type="email" name="email" placeholder="example@gmail.com" required>
                            </div>
                        </div>
                        <div class="input-group">
                            <label class="input-label">Password</label>
                            <div class="input-eye-wrapper">
                                <img class="input-icon" src="https://cdn-icons-png.flaticon.com/128/345/345535.png" alt="">
                                <input id="signInPassword" class="input-box" type="password" name="password" placeholder="Enter your password" required>
                                <button type="button" class="eye-toggle" onclick="togglePassword()">
                                    <img id="eyeIcon" src="https://cdn-icons-png.flaticon.com/128/2767/2767146.png" alt="toggle">
                                </button>
                            </div>
                        </div>
                        <div class="options-row">
                            <div class="checkbox-group">
                                <input type="checkbox" id="rememberMe">
                                <label for="rememberMe">Remember me</label>
                            </div>
                            <button type="button" class="forgot-link" onclick="openForgotModal()">Forgot Password?</button>
                        </div>
                        <button class="submit-btn" type="submit">Sign In</button>
                    </form>
                    <div class="bottom-text">
                        Don't have account? <a href="sign-up.php">Create one now</a>
                    </div>
                    <!-- Divider -->
                    <div class="divider">
                        <div class="divider-line"></div>
                        <span class="divider-text">OR</span>
                        <div class="divider-line"></div>
                    </div>
                    <!-- Guest Portal Button -->
                    <button class="guest-btn" type="button" onclick="location.href='guest-portal.php'">
                        <span class="guest-btn-icon">i</span>
                        <span>View Guest Portal</span>
                    </button>
                    <p class="guest-btn-subtitle">Learn about our services and barangay information</p>
                </div>
            </div>
        </div>
    </div>
<script>
function togglePassword() {
    const input = document.getElementById('signInPassword');
    const icon = document.getElementById('eyeIcon');
    const openIcon = 'https://cdn-icons-png.flaticon.com/128/709/709612.png';
    const closedIcon = 'https://cdn-icons-png.flaticon.com/128/2767/2767146.png';
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.src = openIcon;
    } else {
        input.type = 'password';
        icon.src = closedIcon;
    }
}

// ============================================
// FORGOT PASSWORD MODAL VARIABLES
// ============================================
let generatedOTP = '';
let currentStep = 'email';
let userEmail = '';
let resetToken = '';

function openForgotModal() {
    document.getElementById('forgotModal').classList.add('show');
    resetModal();
}

function closeForgotModal(event) {
    if (event && event.target !== event.currentTarget) return;
    document.getElementById('forgotModal').classList.remove('show');
    resetModal();
}

function resetModal() {
    currentStep = 'email';
    generatedOTP = '';
    userEmail = '';
    resetToken = '';
    document.getElementById('stepEmail').style.display = 'block';
    document.getElementById('stepOTP').style.display = 'none';
    document.getElementById('stepNewPass').style.display = 'none';
    document.getElementById('modalTitle').textContent = 'Forgot Password';
    document.getElementById('forgotEmail').value = '';
    document.getElementById('otpInput').value = '';
    document.getElementById('newPassword').value = '';
    document.getElementById('confirmPassword').value = '';
    hideAlerts();
}

function showError(message) {
    const errorEl = document.getElementById('modalError');
    errorEl.textContent = message;
    errorEl.style.display = 'block';
    document.getElementById('modalSuccess').style.display = 'none';
}

function showSuccess(message) {
    const successEl = document.getElementById('modalSuccess');
    successEl.textContent = message;
    successEl.style.display = 'block';
    document.getElementById('modalError').style.display = 'none';
}

function hideAlerts() {
    document.getElementById('modalError').style.display = 'none';
    document.getElementById('modalSuccess').style.display = 'none';
}

// ============================================
// HANDLE SEND OTP
// ============================================
async function handleSendOTP(event) {
    event.preventDefault();
    hideAlerts();
    
    const email = document.getElementById('forgotEmail').value;
    
    if (!email) {
        showError('Please enter your email address.');
        return;
    }
    
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        showError('Please enter a valid email address.');
        return;
    }
    
    const submitBtn = event.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.textContent = 'Sending...';
    
    try {
        console.log('📧 Sending OTP request for:', email);
        
        const response = await fetch('send_otp.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ email: email })
        });
        
        console.log('✓ Response status:', response.status);
        const data = await response.json();
        console.log('✓ Response data:', data);
        
        if (data.success) {
            userEmail = email;
            
            document.getElementById('stepEmail').style.display = 'none';
            document.getElementById('stepOTP').style.display = 'block';
            document.getElementById('modalTitle').textContent = 'Verify OTP';
            document.getElementById('emailDisplay').textContent = email;
            showSuccess(`✓ OTP sent to ${email}. Check your inbox!`);
            currentStep = 'otp';
            
            setTimeout(() => {
                document.getElementById('otpInput').focus();
            }, 100);
        } else {
            showError(data.error || 'Failed to send OTP. Please try again.');
        }
    } catch (error) {
        console.error('❌ Fetch error:', error);
        showError('Network error: ' + error.message);
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    }
}

// ============================================
// HANDLE VERIFY OTP
// ============================================
async function handleVerifyOTP(event) {
    event.preventDefault();
    hideAlerts();
    
    const enteredOTP = document.getElementById('otpInput').value;
    
    if (!enteredOTP) {
        showError('Please enter the OTP.');
        return;
    }
    
    if (!/^\d{6}$/.test(enteredOTP)) {
        showError('OTP must be exactly 6 digits.');
        return;
    }
    
    const submitBtn = event.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.textContent = 'Verifying...';
    
    try {
        console.log('🔐 Verifying OTP...');
        
        const response = await fetch('verify_otp.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                email: userEmail,
                otp: enteredOTP
            })
        });
        
        console.log('✓ Verify status:', response.status);
        const data = await response.json();
        console.log('✓ Verify data:', data);
        
        if (data.success) {
            resetToken = data.reset_token;
            
            document.getElementById('stepOTP').style.display = 'none';
            document.getElementById('stepNewPass').style.display = 'block';
            document.getElementById('modalTitle').textContent = 'Reset Password';
            showSuccess('✓ OTP verified! Set your new password.');
            currentStep = 'newpass';
            
            setTimeout(() => {
                document.getElementById('newPassword').focus();
            }, 100);
        } else {
            showError(data.error || 'Invalid OTP. Please try again.');
        }
    } catch (error) {
        console.error('❌ Verify error:', error);
        showError('Network error: ' + error.message);
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    }
}

// ============================================
// RESEND OTP
// ============================================
async function resendOTP() {
    hideAlerts();
    
    if (!userEmail) {
        showError('Email not found. Please start over.');
        return;
    }
    
    try {
        console.log('🔄 Resending OTP...');
        
        const response = await fetch('send_otp.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ email: userEmail })
        });
        
        const data = await response.json();
        console.log('✓ Resend data:', data);
        
        if (data.success) {
            showSuccess('✓ New OTP sent! Check your email.');
            document.getElementById('otpInput').value = '';
            document.getElementById('otpInput').focus();
        } else {
            showError(data.error || 'Failed to resend OTP.');
        }
    } catch (error) {
        console.error('❌ Resend error:', error);
        showError('Network error: ' + error.message);
    }
}

// ============================================
// HANDLE RESET PASSWORD
// ============================================
async function handleResetPassword(event) {
    event.preventDefault();
    hideAlerts();
    
    const newPass = document.getElementById('newPassword').value;
    const confirmPass = document.getElementById('confirmPassword').value;
    
    if (!newPass || !confirmPass) {
        showError('Please fill in all fields.');
        return;
    }
    
    if (newPass.length < 6) {
        showError('Password must be at least 6 characters long.');
        return;
    }
    
    if (newPass !== confirmPass) {
        showError('Passwords do not match.');
        return;
    }
    
    const submitBtn = event.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.textContent = 'Resetting...';
    
    try {
        console.log('🔑 Resetting password...');
        
        const response = await fetch('reset_password.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                email: userEmail,
                password: newPass,
                reset_token: resetToken
            })
        });
        
        console.log('✓ Reset status:', response.status);
        const data = await response.json();
        console.log('✓ Reset data:', data);
        
        if (data.success) {
            showSuccess('✅ Password reset successfully! Redirecting...');
            
            setTimeout(() => {
                closeForgotModal();
                document.querySelector('form[action="sign-in.php"]').reset();
            }, 2000);
        } else {
            showError(data.error || 'Failed to reset password.');
        }
    } catch (error) {
        console.error('❌ Reset error:', error);
        showError('Network error: ' + error.message);
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    }
}

// ============================================
// OTP INPUT - Only allow numbers
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    const otpInput = document.getElementById('otpInput');
    if (otpInput) {
        otpInput.addEventListener('input', function(e) {
            this.value = this.value.replace(/\D/g, '').slice(0, 6);
        });
    }
});

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('forgotModal');
        if (modal && modal.classList.contains('show')) {
            closeForgotModal();
        }
    }
});
    </script>
</body>
</html>