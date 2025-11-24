<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "users";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $first_name = trim($_POST['first_name']);
    $middle_name = trim($_POST['middle_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $barangay = trim($_POST['barangay']);
    $id_type = $_POST['id_type'];
    $resident_type = isset($_POST['resident_type']) ? $_POST['resident_type'] : '';
    $file_path = "";

    // Validation
    if (empty($first_name) || empty($last_name) || empty($email) || empty($password) || empty($barangay) || empty($id_type)) {
        $error = "Please fill in all required fields.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (empty($resident_type)) {
        $error = "Please select Resident or Non Resident.";
    } else {
        // Handle file upload
        if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
            $upload_dir = 'uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            // Generate unique filename
            $file_extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
            $file_name = uniqid() . '_' . time() . '.' . $file_extension;
            $file_path = $upload_dir . $file_name;
            
            // Validate file type
            $allowed_types = ['jpg', 'jpeg', 'png', 'pdf'];
            if (!in_array(strtolower($file_extension), $allowed_types)) {
                $error = "Invalid file type. Only JPG, PNG, and PDF files are allowed.";
            } elseif ($_FILES['file']['size'] > 5000000) { // 5MB max
                $error = "File size too large. Maximum 5MB allowed.";
            } else {
                if (!move_uploaded_file($_FILES['file']['tmp_name'], $file_path)) {
                    $error = "File upload failed.";
                }
            }
        } else {
            $error = "Please upload a valid ID.";
        }

        if (!$error) {
            // Check if email already exists
            $check_stmt = $conn->prepare("SELECT id FROM account WHERE email = ?");
            $check_stmt->bind_param("s", $email);
            $check_stmt->execute();
            $check_stmt->store_result();
            
            if ($check_stmt->num_rows > 0) {
                $error = "Email already exists. Please use a different email.";
                $check_stmt->close();
            } else {
                $check_stmt->close();
                
                // Insert into database
                $stmt = $conn->prepare("INSERT INTO account (first_name, middle_name, last_name, email, password, barangay, id_type, resident_type, file_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssssssss", $first_name, $middle_name, $last_name, $email, $password, $barangay, $id_type, $resident_type, $file_path);
                
                if ($stmt->execute()) {
                    $success = "Account created successfully! Redirecting to sign in...";
                    echo "<script>setTimeout(function(){ window.location.href='sign-in.php'; }, 2000);</script>";
                } else {
                    $error = "Error creating account. Please try again.";
                }
                $stmt->close();
            }
        }
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eBCsH - Sign Up</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background: white;
            min-height: 100vh;
            overflow-x: hidden;
        }
        
        .container {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }
        
        /* Left Panel */
        .left-panel {
            flex: 1;
            min-width: 320px;
            background: linear-gradient(135deg, #a3c3ad 0%, #22594b 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2.5rem;
        }
        
        .logo-img {
            width: 270px;
            max-width: 48%;
            border-radius: 130px;
            margin-bottom: 2rem;
        }
        
        .welcome-text {
            color: white;
            text-align: center;
            margin-top: 0.75rem;
            line-height: 1.6;
            font-size: 1rem;
        }
        
        /* Right Panel */
        .right-panel {
            flex: 1;
            min-width: 420px;
            background: white;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 3.5rem;
            overflow-y: auto;
        }
        
        .form-wrapper {
            width: 100%;
            max-width: 760px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        /* Tab Switcher */
        .tab-group {
            display: flex;
            width: 100%;
            background: #f5f6fa;
            border-radius: 1rem;
            margin-bottom: 1.75rem;
            overflow: hidden;
            height: 3.5rem;
            align-items: center;
        }
        
        .tab-btn {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            background: transparent;
            color: #5d7c76;
            border: none;
            border-radius: 1rem;
            height: 2.5rem;
            margin: 0 0.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 1rem;
            font-weight: 500;
        }
        
        .tab-btn:hover {
            background: rgba(255, 255, 255, 0.5);
        }
        
        .tab-btn.active {
            background: white;
            color: #22594b;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        /* Form Header */
        .form-header {
            width: 100%;
            text-align: left;
            margin-bottom: 0.5rem;
            margin-top: 0.5rem;
        }
        
        .form-title {
            font-size: 1.25rem;
            margin-bottom: 0.25rem;
            font-weight: 600;
        }
        
        .form-subtitle {
            font-size: 0.875rem;
            color: #888;
            margin-bottom: 1.5rem;
        }
        
        /* Error/Success Messages */
        .error {
            color: #ef4444;
            font-size: 0.875rem;
            margin-bottom: 0.75rem;
            background: #fee2e2;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
        }
        
        .success {
            color: #16a34a;
            font-size: 0.875rem;
            margin-bottom: 0.75rem;
            background: #d1fae5;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
        }
        
        /* Form Grid */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.25rem 1.5rem;
        }
        
        .form-grid .full-width {
            grid-column: 1 / -1;
        }
        
        /* Input Groups */
        .input-group {
            width: 100%;
        }
        
        .input-label {
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
            display: block;
            color: #333;
        }
        
        .input-wrapper {
            position: relative;
            width: 100%;
        }
        
        .input-box {
            width: 100%;
            padding: 0.75rem;
            border-radius: 0.5rem;
            border: none;
            background: #f5f6fa;
            outline: none;
            font-size: 1rem;
            transition: box-shadow 0.2s;
        }
        
        .input-box:focus {
            box-shadow: 0 0 0 2px #dbeafe;
        }
        
        .input-box.with-icon {
            padding-left: 2.75rem;
        }
        
        .input-box.with-right-icon {
            padding-right: 2.75rem;
        }
        
        .input-icon {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            width: 18px;
            height: 18px;
            opacity: 0.7;
            pointer-events: none;
        }
        
        .eye-toggle {
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            width: 18px;
            height: 18px;
            cursor: pointer;
            opacity: 0.85;
            background: transparent;
            border: none;
            padding: 0;
        }
        
        .eye-toggle img {
            width: 100%;
            height: 100%;
        }
        
        /* Select Dropdown */
        select.input-box {
            appearance: none;
            cursor: pointer;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23666' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-color: #f5f6fa;
        }
        
        /* Upload Box */
        .upload-box {
            height: 110px;
            border-radius: 0.5rem;
            background: #f5f6fa;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px dashed rgba(0, 0, 0, 0.06);
        }
        
        .upload-btn {
            padding: 0.375rem 0.75rem;
            border-radius: 0.25rem;
            border: 1px solid #888;
            background: white;
            cursor: pointer;
            font-size: 0.875rem;
        }
        
        #fileInput {
            display: none;
        }
        
        /* Checkbox Group */
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }
        
        .checkbox-group input[type="checkbox"] {
            margin-right: 0.5rem;
            accent-color: #22594b;
            cursor: pointer;
        }
        
        .checkbox-group label {
            font-size: 0.875rem;
            cursor: pointer;
        }
        
        /* Terms */
        .terms-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 0.75rem;
        }
        
        .terms-group input[type="checkbox"] {
            margin-right: 0.5rem;
            accent-color: #22594b;
            cursor: pointer;
        }
        
        .terms-group label {
            font-size: 0.875rem;
        }
        
        .terms-link {
            color: #22594b;
            text-decoration: underline;
            cursor: pointer;
            background: transparent;
            border: none;
            padding: 0;
            font-family: inherit;
            font-size: inherit;
        }
        
        /* Submit Button */
        .submit-btn {
            width: 100%;
            padding: 0.875rem;
            border-radius: 0.5rem;
            border: none;
            background: linear-gradient(to bottom, #163832, #194f43);
            color: white;
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            font-weight: 600;
            font-size: 0.9375rem;
            transition: background 0.2s;
        }
        
        .submit-btn:hover {
            background: #22594b;
        }
        
        /* Bottom Text */
        .bottom-text {
            width: 100%;
            text-align: center;
            margin-top: 0.75rem;
            color: #666;
        }
        
        .bottom-text button {
            color: #22594b;
            cursor: pointer;
            background: transparent;
            border: none;
            font-family: inherit;
            font-size: inherit;
            text-decoration: underline;
        }
        
        /* Modal */
        .modal {
            display: none;
            position: fixed;
            inset: 0;
            backdrop-filter: blur(4px);
            background: rgba(255, 255, 255, 0.2);
            z-index: 50;
            padding: 1rem;
            align-items: center;
            justify-content: center;
        }
        
        .modal.show {
            display: flex;
        }
        
        .modal-content {
            background: white;
            border-radius: 0.75rem;
            max-width: 42rem;
            width: 100%;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }
        
        .modal-header {
            position: sticky;
            top: 0;
            background: linear-gradient(135deg, #22594b 0%, #194f43 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 0.75rem 0.75rem 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-title {
            font-size: 1.25rem;
            font-weight: 600;
        }
        
        .modal-close {
            color: white;
            font-size: 2rem;
            line-height: 1;
            background: transparent;
            border: none;
            cursor: pointer;
            width: 2rem;
            height: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.2s;
        }
        
        .modal-close:hover {
            color: #d1d5db;
        }
        
        .modal-body {
            padding: 1.5rem;
            color: #374151;
            text-align: center;
        }
        
        .modal-section {
            margin-bottom: 1.25rem;
        }
        
        .modal-section h3 {
            color: #22594b;
            font-weight: 600;
            margin-bottom: 0.5rem;
            font-size: 1rem;
        }
        
        .modal-section p {
            font-size: 0.875rem;
            line-height: 1.6;
        }
        
        .modal-footer {
            position: sticky;
            bottom: 0;
            background: #f9fafb;
            padding: 1rem;
            border-radius: 0 0 0.75rem 0.75rem;
            border-top: 1px solid #e5e7eb;
        }
        
        .modal-footer button {
            width: 100%;
            padding: 0.75rem;
            border-radius: 0.5rem;
            background: linear-gradient(to bottom, #163832, #194f43);
            color: white;
            cursor: pointer;
            border: none;
            transition: opacity 0.2s;
        }
        
        .modal-footer button:hover {
            opacity: 0.9;
        }
        
        /* Responsive */
        @media (max-width: 1024px) {
            .container {
                flex-direction: column;
            }
            
            .left-panel {
                min-height: 300px;
            }
            
            .right-panel {
                min-width: auto;
            }
        }
        
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .left-panel {
                padding: 2rem 1rem;
            }
            
            .right-panel {
                padding: 2rem 1rem;
            }
            
            .logo-img {
                width: 180px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Left Panel -->
        <div class="left-panel">
            <img class="logo-img" src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRTDCuh4kIpAtR-QmjA1kTjE_8-HSd8LSt3Gw&s" alt="Robot">
            <div class="welcome-text">
                Welcome to BDCDRS<br>Your friendly assistant is here to help!
            </div>
        </div>

        <!-- Right Panel -->
        <div class="right-panel">
            <div class="form-wrapper">
                <!-- Tabs -->
                <div class="tab-group">
                    <button class="tab-btn" onclick="window.location.href='sign-in.php'">
                        Sign In
                    </button>
                    <button class="tab-btn active">
                        Sign Up
                    </button>
                </div>

                <div class="form-header">
                    <h2 class="form-title">Create Account</h2>
                    <p class="form-subtitle">Join us and get started today</p>
                </div>

                <div style="width: 100%;">
                    <?php if ($error): ?>
                        <div class="error"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                        <div class="success"><?php echo htmlspecialchars($success); ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="sign-up.php" enctype="multipart/form-data">
                        <div class="form-grid">
                            <!-- First Name -->
                            <div>
                                <div class="input-group">
                                    <label class="input-label">First Name</label>
                                    <input class="input-box" type="text" name="first_name" placeholder="First Name" required value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>">
                                </div>
                            </div>

                            <!-- Middle Name -->
                            <div>
                                <div class="input-group">
                                    <label class="input-label">Middle Name</label>
                                    <input class="input-box" type="text" name="middle_name" placeholder="Middle Name" value="<?php echo isset($_POST['middle_name']) ? htmlspecialchars($_POST['middle_name']) : ''; ?>">
                                </div>
                            </div>

                            <!-- Last Name -->
                            <div>
                                <div class="input-group">
                                    <label class="input-label">Last Name</label>
                                    <input class="input-box" type="text" name="last_name" placeholder="Last Name" required value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>">
                                </div>
                            </div>

                            <!-- Email - Full Width -->
                            <div class="full-width">
                                <div class="input-group">
                                    <label class="input-label">Email Address</label>
                                    <div class="input-wrapper">
                                        <img class="input-icon" src="https://img.icons8.com/ios-filled/50/000000/new-post.png" alt="">
                                        <input class="input-box with-icon" type="email" name="email" placeholder="example@gmail.com (must be a real existing gmail)" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                                    </div>
                                </div>
                            </div>

                            <!-- Password -->
                            <div>
                                <div class="input-group">
                                    <label class="input-label">Password</label>
                                    <div class="input-wrapper">
                                        <img class="input-icon" src="https://cdn-icons-png.flaticon.com/128/345/345535.png" alt="">
                                        <input id="password" class="input-box with-icon with-right-icon" type="password" name="password" placeholder="Create a password" required>
                                        <button type="button" class="eye-toggle" onclick="togglePassword('password', 'eyePassword')">
                                            <img id="eyePassword" src="https://cdn-icons-png.flaticon.com/128/2767/2767146.png" alt="">
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Barangay -->
                            <div>
                                <div class="input-group">
                                    <label class="input-label">Barangay</label>
                                    <input class="input-box" type="text" name="barangay" placeholder="Address" value="<?php echo isset($_POST['barangay']) ? htmlspecialchars($_POST['barangay']) : ''; ?>">
                                </div>
                            </div>

                            <!-- Confirm Password -->
                            <div>
                                <div class="input-group">
                                    <label class="input-label">Confirm Password</label>
                                    <div class="input-wrapper">
                                        <img class="input-icon" src="https://cdn-icons-png.flaticon.com/128/345/345535.png" alt="">
                                        <input id="confirmPassword" class="input-box with-icon with-right-icon" type="password" name="confirm_password" placeholder="Confirm your password" required>
                                        <button type="button" class="eye-toggle" onclick="togglePassword('confirmPassword', 'eyeConfirm')">
                                            <img id="eyeConfirm" src="https://cdn-icons-png.flaticon.com/128/2767/2767146.png" alt="">
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Empty cell -->
                            <div></div>

                            <!-- Upload Files -->
                            <div>
                                <div class="input-group">
                                    <label class="input-label">Upload Valid ID</label>
                                    <div class="upload-box">
                                        <input type="file" id="fileInput" name="file" accept=".jpg,.jpeg,.png,.pdf" required>
                                        <label for="fileInput" class="upload-btn" id="fileLabel">Browse File</label>
                                    </div>
                                </div>
                            </div>

                            <!-- Type of ID -->
                            <div>
                                <div class="input-group">
                                    <label class="input-label">Type of ID</label>
                                    <div class="input-wrapper">
                                        <img class="input-icon" src="https://cdn-icons-png.flaticon.com/128/2659/2659360.png" alt="">
                                        <select class="input-box with-icon" name="id_type" required>
                                            <option value="">Select ID Type</option>
                                            <option value="government-id">Government ID</option>
                                            <option value="drivers-license">Driver's License</option>
                                            <option value="passport">Passport</option>
                                            <option value="postal-id">Postal ID</option>
                                            <option value="voters-id">Voter's ID</option>
                                            <option value="senior-citizen-id">Senior Citizen ID</option>
                                            <option value="pwd-id">PWD ID</option>
                                            <option value="philhealth-id">PhilHealth ID</option>
                                            <option value="sss-id">SSS ID</option>
                                            <option value="umid">UMID</option>
                                        </select>
                                    </div>
                                    
                                    <!-- Resident/Non-Resident -->
                                    <div class="checkbox-group">
                                        <input type="checkbox" id="resident" name="resident_type" value="resident" onchange="handleResidentChange(this)">
                                        <label for="resident">Resident</label>
                                        <input type="checkbox" id="non-resident" name="resident_type" value="non-resident" onchange="handleNonResidentChange(this)">
                                        <label for="non-resident">Non Resident</label>
                                    </div>
                                </div>
                            </div>

                            <!-- Terms - Full Width -->
                            <div class="full-width">
                                <div class="terms-group">
                                    <input type="checkbox" id="terms" required>
                                    <label for="terms">
                                        I agree to the 
                                        <button type="button" class="terms-link" onclick="showModal()">
                                            Terms and Conditions
                                        </button>
                                    </label>
                                </div>
                            </div>

                            <!-- Submit Button - Full Width -->
                            <div class="full-width">
                                <button type="submit" class="submit-btn">
                                    Create Account
                                </button>
                            </div>
                        </div>
                    </form>

                    <div class="bottom-text">
                        Already have an account? <button onclick="window.location.href='sign-in.php'">Sign in instead</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Terms Modal -->
    <div id="termsModal" class="modal" onclick="hideModal()">
        <div class="modal-content" onclick="event.stopPropagation()">
            <div class="modal-header">
                <h2 class="modal-title">Privacy & Confidentiality Terms</h2>
                <button class="modal-close" onclick="hideModal()">×</button>
            </div>
            
            <div class="modal-body">
                <div class="modal-section">
                    <h3>1. Privacy Commitment</h3>
                    <p>Our website is committed to protecting your privacy. Any personal information you provide while using our services will remain strictly confidential.</p>
                </div>

                <div class="modal-section">
                    <h3>2. No Data Sharing</h3>
                    <p>We do not sell, share, or distribute your information to any third party without your explicit consent, unless required by law.</p>
                </div>

                <div class="modal-section">
                    <h3>3. Secure Protection of Information</h3>
                    <p>We use industry-standard security measures to protect your data from unauthorized access, leaks, or misuse. Your information is stored securely and handled with utmost care.</p>
                </div>

                <div class="modal-section">
                    <h3>4. Controlled Access</h3>
                    <p>Only authorized staff members—who are bound by confidentiality agreements—may access your information when necessary to provide support or service.</p>
                </div>

                <div class="modal-section">
                    <h3>5. User Rights</h3>
                    <p>You have full control over your data. You may request to access, update, or delete your information at any time by contacting us.</p>
                </div>

                <div class="modal-section">
                    <h3>6. Responsible Use</h3>
                    <p>Any information you provide will only be used for the purposes outlined on this website (such as account creation, service delivery, or communication). We will never use your data for unapproved activities.</p>
                </div>

                <div class="modal-section">
                    <h3>7. Data Retention</h3>
                    <p>We retain your information only for as long as needed to provide our services or to comply with legal obligations. After this period, your data will be safely removed from our systems.</p>
                </div>

                <div class="modal-section">
                    <h3>8. Updates to This Policy</h3>
                    <p>We may update these Terms from time to time to reflect improvements in our services or changes in legal requirements. Any updates will be posted on this page, and continued use of the website will constitute your acceptance of the changes.</p>
                </div>
            </div>
            
            <div class="modal-footer">
                <button onclick="hideModal()">Close</button>
            </div>
        </div>
    </div>

    <script>
        // Toggle Password Visibility
        function togglePassword(inputId, eyeId) {
            const input = document.getElementById(inputId);
            const eye = document.getElementById(eyeId);
            const openIcon = 'https://cdn-icons-png.flaticon.com/128/709/709612.png';
            const closedIcon = 'https://cdn-icons-png.flaticon.com/128/2767/2767146.png';
            
            if (input.type === 'password') {
                input.type = 'text';
                eye.src = openIcon;
            } else {
                input.type = 'password';
                eye.src = closedIcon;
            }
        }

        // File Input Handler
        const fileInput = document.getElementById('fileInput');
        const fileLabel = document.getElementById('fileLabel');
        
        fileInput.addEventListener('change', function() {
            if (fileInput.files && fileInput.files.length > 0) {
                fileLabel.textContent = fileInput.files[0].name;
            } else {
                fileLabel.textContent = 'Browse File';
            }
        });

        // Resident/Non-Resident Checkbox Handling
        function handleResidentChange(checkbox) {
            if (checkbox.checked) {
                document.getElementById('non-resident').checked = false;
            }
        }

        function handleNonResidentChange(checkbox) {
            if (checkbox.checked) {
                document.getElementById('resident').checked = false;
            }
        }

        // Modal Functions
        function showModal() {
            document.getElementById('termsModal').classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function hideModal() {
            document.getElementById('termsModal').classList.remove('show');
            document.body.style.overflow = 'auto';
        }

        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                hideModal();
            }
        });
    </script>
</body>

</html>
