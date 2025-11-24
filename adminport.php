<?php
session_start();
require_once 'audit_trail_helper.php';

// Handle login
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Database connection
    $servername = "localhost";
    $username_db = "root";
    $password_db = "";
    $dbname = "users";

    $conn = new mysqli($servername, $username_db, $password_db, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Verify admin credentials
    $stmt = $conn->prepare("SELECT id, email, password, usertype FROM account WHERE email = ? AND usertype = 'admin'");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Verify password (plain text comparison)
        if ($password === $user['password']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['is_admin'] = true;

            // Log admin login
            logAdminLogin($user['id'], $user['email']);

            $stmt->close();
            $conn->close();
            
            header("Location: admindashboard.php");
            exit();
        }
    }

    $stmt->close();
    $conn->close();
    
    $error_message = "Invalid email or password";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Portal - Barangay Officials Access</title>
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="css/adminport.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
            font-family: 'Poppins', sans-serif;
            background: #ffffff;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .page {
            display: flex;
            width: 100%;
            min-height: 100vh;
            overflow: hidden;
        }

        /* Left Panel - Improved */
        .left {
            flex: 1;
            min-width: 420px;
            background: linear-gradient(135deg, #1a5f54 0%, #0d3d37 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 60px 48px;
            gap: 24px;
            position: relative;
            overflow: hidden;
        }

        .left::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.05) 1px, transparent 1px);
            background-size: 50px 50px;
            opacity: 0.3;
        }

        .seal {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 20px 60px rgba(0,0,0,0.25);
            flex-shrink: 0;
            position: relative;
            z-index: 1;
            transition: transform 0.3s ease;
        }

        .seal:hover {
            transform: scale(1.05);
        }

        .seal img { 
            width: 140px; 
            height: 140px; 
            object-fit: contain; 
            display: block; 
        }

        .left-title {
            color: #ffffff;
            font-family: 'Montserrat', sans-serif;
            font-size: 38px;
            font-weight: 700;
            margin: 16px 0 8px;
            text-align: center;
            position: relative;
            z-index: 1;
            letter-spacing: -0.5px;
        }

        .left-sub {
            color: rgba(255,255,255,0.9);
            font-size: 17px;
            text-align: center;
            max-width: 520px;
            line-height: 1.6;
            position: relative;
            z-index: 1;
        }

        .left-pill {
            margin-top: 32px;
            width: 85%;
            max-width: 480px;
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            border-radius: 50px;
            padding: 18px 32px;
            color: #fff;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.15);
            text-align: center;
            font-size: 16px;
            position: relative;
            z-index: 1;
            border: 1px solid rgba(255,255,255,0.1);
        }

        .left-pill::before {
            content: '🔒';
            font-size: 20px;
        }

        /* Right Panel - Improved */
        .right {
            flex: 1;
            min-width: 520px;
            background: #ffffff;
            padding: 60px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .form-container {
            width: 100%;
            max-width: 480px;
        }

        .brand {
            display: flex;
            gap: 16px;
            align-items: center;
            width: 100%;
            margin-bottom: 12px;
        }

        .brand .shield {
            width: 72px;
            height: 72px;
            border-radius: 16px;
            background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        .brand .shield img { 
            width: 48px; 
            height: 48px; 
        }

        .brand-text {
            flex: 1;
        }

        .heading {
            font-family: 'Montserrat', sans-serif;
            font-size: 32px;
            font-weight: 700;
            color: #1a1a1a;
            margin: 0;
            line-height: 1.2;
            letter-spacing: -0.5px;
        }

        .subheading {
            font-size: 15px;
            color: #6b7280;
            margin-top: 4px;
            font-weight: 500;
        }

        .desc {
            font-size: 15px;
            color: #6b7280;
            margin: 16px 0 28px;
            line-height: 1.6;
        }

        /* Error Alert */
        .error-alert {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
            padding: 14px 16px;
            border-radius: 12px;
            margin-bottom: 24px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .error-alert::before {
            content: '⚠️';
            font-size: 18px;
        }

        /* Form Styling */
        .form { 
            width: 100%; 
        }

        .field-label {
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin: 0 0 8px 0;
            display: block;
        }

        .input-wrap {
            position: relative;
            margin-bottom: 20px;
            width: 100%;
        }

        .input {
            width: 100%;
            padding: 14px 16px 14px 48px;
            border-radius: 12px;
            border: 2px solid #e5e7eb;
            background: #ffffff;
            font-size: 15px;
            color: #1f2937;
            outline: none;
            transition: all 0.2s ease;
            font-family: 'Poppins', sans-serif;
        }

        .input:focus {
            border-color: #1a5f54;
            box-shadow: 0 0 0 4px rgba(26, 95, 84, 0.1);
        }

        .input::placeholder {
            color: #9ca3af;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            width: 20px;
            height: 20px;
            opacity: 0.5;
            pointer-events: none;
        }

        .eye-btn {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            width: 36px;
            height: 36px;
            border-radius: 8px;
            background: transparent;
            border: 0;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            transition: background 0.2s ease;
        }

        .eye-btn:hover {
            background: #f3f4f6;
        }

        .eye-btn img { 
            width: 18px; 
            height: 18px; 
            opacity: 0.6;
        }

        .forgot {
            font-size: 14px;
            color: #1a5f54;
            margin-top: -8px;
            margin-bottom: 24px;
            text-align: right;
            width: 100%;
            font-weight: 600;
            cursor: pointer;
            transition: color 0.2s ease;
        }

        .forgot:hover {
            color: #0d3d37;
            text-decoration: underline;
        }

        .actions {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 8px;
        }

        .primary {
            width: 100%;
            padding: 16px 24px;
            border-radius: 12px;
            border: 0;
            background: linear-gradient(135deg, #1a5f54 0%, #0d3d37 100%);
            color: #fff;
            font-weight: 700;
            font-size: 16px;
            cursor: pointer;
            box-shadow: 0 4px 16px rgba(26, 95, 84, 0.3);
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
        }

        .primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 24px rgba(26, 95, 84, 0.4);
        }

        .primary:active {
            transform: translateY(0);
        }

        .secondary {
            width: 100%;
            padding: 16px 24px;
            border-radius: 12px;
            border: 2px solid #e5e7eb;
            background: #ffffff;
            color: #374151;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            font-family: 'Poppins', sans-serif;
        }

        .secondary:hover {
            background: #f9fafb;
            border-color: #d1d5db;
        }

        .link-row { 
            width: 100%; 
            display: flex; 
            justify-content: center; 
            margin-top: 20px; 
        }

        .link-row a { 
            color: #1a5f54; 
            text-decoration: none; 
            font-weight: 600; 
            font-size: 14px;
            transition: color 0.2s ease;
        }

        .link-row a:hover {
            color: #0d3d37;
            text-decoration: underline;
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .page { 
                flex-direction: column; 
            }
            
            .left { 
                min-height: 400px; 
                width: 100%; 
                padding: 40px 24px;
                min-width: auto;
            }
            
            .right { 
                width: 100%; 
                min-width: auto; 
                padding: 40px 24px;
            }
            
            .left-pill { 
                width: 100%; 
            }
            
            .form-container { 
                max-width: 100%; 
            }
        }

        @media (max-width: 640px) {
            .left {
                padding: 32px 20px;
                min-height: 350px;
            }

            .seal {
                width: 160px;
                height: 160px;
            }

            .seal img {
                width: 110px;
                height: 110px;
            }

            .left-title {
                font-size: 28px;
            }

            .left-sub {
                font-size: 15px;
            }

            .right {
                padding: 32px 20px;
            }

            .heading {
                font-size: 26px;
            }

            .brand .shield {
                width: 60px;
                height: 60px;
            }

            .brand .shield img {
                width: 40px;
                height: 40px;
            }
        }
    </style>
</head>
<body>
    <div class="page" role="main">
        <div class="left" aria-hidden="true">
            <div class="seal" title="Municipal Seal">
                <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRTDCuh4kIpAtR-QmjA1kTjE_8-HSd8LSt3Gw&s" alt="seal">
            </div>
            <div class="left-title">Welcome to BCDRS</div>
            <div class="left-sub">Barangay Official! Please sign in to access your dashboard and manage community requests.</div>
            <div class="left-pill">Admin Portal - Barangay Officials Only</div>
        </div>
        <aside class="right" aria-label="Admin sign in">
            <div class="form-container">
                <div class="brand">
                    <div class="shield" aria-hidden="true">
                        <img src="https://cdn-icons-png.flaticon.com/128/10703/10703030.png" alt="">
                    </div>
                    <div class="brand-text">
                        <div class="heading">Admin Sign In</div>
                        <div class="subheading">Barangay Official Dashboard Access</div>
                    </div>
                </div>
                <div class="desc">Secure access for authorized barangay officials to manage community services and requests.</div>

                <?php if (isset($error_message)): ?>
                    <div class="error-alert">
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>
                <form class="form" method="POST" action="">
                    <div class="field-label">Email Address</div>
                    <div class="input-wrap">
                        <img class="input-icon" src="https://img.icons8.com/ios-filled/50/9aa0a6/new-post.png" alt="">
                        <input id="officialEmail" name="email" class="input" type="email" placeholder="official@barangay.gov.ph" required>
                    </div>
                    <div class="field-label">Password</div>
                    <div class="input-wrap">
                        <img class="input-icon" src="https://img.icons8.com/ios-filled/50/9aa0a6/lock-2.png" alt="">
                        <input id="adminPassword" name="password" class="input" type="password" placeholder="Enter your secure password" required>
                        <button type="button" class="eye-btn" id="eyeToggle" aria-label="toggle password">
                            <img src="https://cdn-icons-png.flaticon.com/128/2767/2767146.png" alt="toggle">
                        </button>
                    </div>
                    <div class="forgot">Forgot your password?</div>
                    <div class="actions">
                        <button class="primary" type="submit">Sign in to Dashboard</button>
                        <button class="secondary" type="button" onclick="location.href='sign-in.php'">Back to Citizen Portal</button>
                    </div>
                </form>
            </div>
        </aside>
    </div>
    <script>
        (function(){
            const pwd = document.getElementById('adminPassword');
            const eye = document.getElementById('eyeToggle');
            if (!pwd || !eye) return;
            const openIcon = 'https://cdn-icons-png.flaticon.com/128/709/709612.png';
            const closedIcon = 'https://cdn-icons-png.flaticon.com/128/2767/2767146.png';
            eye.addEventListener('click', function () {
                const showing = pwd.type === 'password';
                pwd.type = showing ? 'text' : 'password';
                const img = eye.querySelector('img');
                if (img) img.src = showing ? openIcon : closedIcon;
            });
        })();
    </script>
</body>
</html>