<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit();
}

// Handle logout
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['logout'])) {
    session_destroy();
    header("Location: sign-in.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "users";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get user data
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM account WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    header("Location: signin.php");
    exit();
}

// Check if user is admin
$is_admin = ($user['usertype'] === 'admin');

// Get join date (use created_at if available, otherwise use current date)
$join_date = isset($user['created_at']) ? date('F j, Y', strtotime($user['created_at'])) : date('F j, Y');

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Barangay 170</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            min-height: 100vh;
            background: #DAF1DE;
        }

        /* Header Styles */
        header {
            background: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 2rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .logo-section {
            display: flex;
            align-items: center;
            cursor: pointer;
            background: transparent;
            border: none;
            transition: opacity 0.3s;
            text-decoration: none;
            color: inherit;
        }

        .logo-section:hover {
            opacity: 0.8;
        }

        .logo-img {
            height: 50px;
            margin-right: 10px;
            border-radius: 50%;
        }

        .logo-text {
            text-align: left;
        }

        .logo-text > div:first-child {
            font-weight: 500;
        }

        .logo-text > div:last-child {
            opacity: 0.7;
            font-size: 0.9rem;
        }

        .logout-btn {
            background: #FD7E7E;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1rem;
            transition: background 0.3s;
        }

        .logout-btn:hover {
            background: #fc6565;
        }

        .logout-btn i {
            margin-right: 6px;
        }

        /* Main Container */
        .main-container {
            max-width: 900px;
            margin: 1.75rem auto;
            padding: 0 1rem;
        }

        /* Profile Card */
        .profile-card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        /* Profile Header */
        .profile-header {
            background: linear-gradient(to right, #16a34a, #15803d);
            padding: 3rem 2rem;
            text-align: center;
        }

        .profile-avatar {
            background: white;
            border-radius: 50%;
            width: 8rem;
            height: 8rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            box-shadow: 0 10px 15px rgba(0,0,0,0.1);
        }

        .profile-avatar i {
            font-size: 4rem;
            color: #16a34a;
        }

        .profile-header h1 {
            color: white;
            font-size: 1.875rem;
            margin-bottom: 0.5rem;
        }

        .profile-email {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            color: #dcfce7;
        }

        /* Profile Info Section */
        .profile-info {
            padding: 2rem;
        }

        .profile-info h2 {
            font-size: 1.5rem;
            color: #14532d;
            margin-bottom: 1.5rem;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .info-card {
            background: #f0fdf4;
            padding: 1.25rem;
            border-radius: 0.5rem;
            border: 1px solid #dcfce7;
        }

        .info-card-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.5rem;
        }

        .info-icon {
            background: #16a34a;
            padding: 0.5rem;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .info-icon i {
            color: white;
            font-size: 1.25rem;
        }

        .info-card h3 {
            color: #15803d;
            margin: 0;
            font-size: 1rem;
        }

        .info-card p {
            color: #166534;
            margin: 0;
            margin-left: 2.75rem;
        }

        .info-card p.capitalize {
            text-transform: capitalize;
        }

        /* Valid ID Image */
        .id-image {
            margin-left: 2.75rem;
            margin-top: 0.5rem;
        }

        .id-image img {
            width: 100%;
            max-width: 200px;
            height: auto;
            border-radius: 0.5rem;
            border: 1px solid #dcfce7;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .id-image p {
            font-size: 0.875rem;
            color: #166534;
            margin-top: 0.5rem;
            margin-left: 0;
        }

        /* Account Status */
        .account-status {
            margin-top: 2rem;
            background: #ecfdf5;
            border-left: 4px solid #16a34a;
            padding: 1.25rem;
            border-radius: 0 0.5rem 0.5rem 0;
        }

        .account-status h3 {
            color: #14532d;
            margin-bottom: 0.5rem;
            font-size: 1rem;
        }

        .status-indicator {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .status-dot {
            width: 0.75rem;
            height: 0.75rem;
            background: #16a34a;
            border-radius: 50%;
        }

        .status-text {
            color: #166534;
        }

        /* Admin Info */
        .admin-info {
            margin-top: 1.5rem;
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 1.25rem;
            border-radius: 0 0.5rem 0.5rem 0;
        }

        .admin-info h3 {
            color: #92400e;
            margin-bottom: 0.5rem;
            font-size: 1rem;
        }

        .admin-info p {
            color: #78350f;
            margin: 0;
        }

        /* Quick Actions */
        .quick-actions {
            margin-top: 1.5rem;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .action-card {
            background: white;
            padding: 1.25rem;
            border-radius: 0.5rem;
            border: 2px solid #dcfce7;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .action-card:hover {
            border-color: #16a34a;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .action-card.logout:hover {
            border-color: #ef4444;
        }

        .action-icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .action-card h3 {
            color: #14532d;
            margin-bottom: 0.25rem;
            font-size: 1rem;
        }

        .action-card.logout h3 {
            color: #dc2626;
        }

        .action-card p {
            font-size: 0.875rem;
            color: #166534;
            margin: 0;
        }

        .action-card.logout p {
            color: #991b1b;
        }

        /* Footer */
        footer {
            background: white;
            border-top: 1px solid #dcfce7;
            margin-top: 3rem;
        }

        .footer-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 2rem 1.5rem;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
            margin-bottom: 1.5rem;
        }

        .footer-section {
            text-align: center;
        }

        .footer-section h3 {
            font-size: 1.125rem;
            color: #14532d;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .footer-content {
            display: inline-block;
            text-align: left;
        }

        .footer-item {
            margin-bottom: 0.75rem;
            font-size: 0.9375rem;
        }

        .footer-label {
            color: #15803d;
            font-weight: 500;
            margin-bottom: 0.25rem;
        }

        .footer-value {
            color: #166534;
            font-size: 0.875rem;
        }

        .emergency-item {
            margin-bottom: 0.75rem;
            font-size: 0.9375rem;
        }

        .emergency-item span:first-child {
            color: #15803d;
            min-width: 80px;
            display: inline-block;
        }

        .emergency-item span:last-child {
            color: #166534;
        }

        .hospital-item {
            margin-bottom: 0.75rem;
        }

        .hospital-name {
            color: #15803d;
            font-weight: 500;
        }

        .hospital-phone {
            color: #166534;
            font-size: 0.875rem;
        }

        .footer-copyright {
            border-top: 1px solid #dcfce7;
            padding-top: 1.5rem;
            text-align: center;
            color: #15803d;
            font-size: 0.9375rem;
        }

        .footer-copyright p {
            margin-bottom: 0.5rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            header {
                flex-direction: column;
                gap: 1rem;
                padding: 1rem;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .quick-actions {
                grid-template-columns: 1fr;
            }

            .footer-grid {
                grid-template-columns: 1fr;
            }

            .profile-header {
                padding: 2rem 1rem;
            }

            .profile-info {
                padding: 1.5rem 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <a href="homepage.php" class="logo-section">
            <img 
                src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRTDCuh4kIpAtR-QmjA1kTjE_8-HSd8LSt3Gw&s" 
                alt="Logo"
                class="logo-img"
            />
            <div class="logo-text">
                <div>Barangay 170</div>
                <div>Community Portal</div>
            </div>
        </a>
        <form method="POST" style="display: inline; margin: 0;">
            <button type="submit" name="logout" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i>Logout
            </button>
        </form>
    </header>

    <div class="main-container">
        <div class="profile-card">
            <!-- Profile Header -->
            <div class="profile-header">
                <div class="profile-avatar">
                    <i class="fas fa-user-circle"></i>
                </div>
                <h1><?php echo htmlspecialchars($user['email']); ?></h1>
                <div class="profile-email">
                    <i class="fas fa-envelope"></i>
                    <span><?php echo htmlspecialchars($user['email']); ?></span>
                </div>
            </div>

            <!-- Profile Info -->
            <div class="profile-info">
                <h2>Profile Information</h2>
                
                <div class="info-grid">
                    <!-- Email -->
                    <div class="info-card">
                        <div class="info-card-header">
                            <div class="info-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <h3>Email Address</h3>
                        </div>
                        <p><?php echo htmlspecialchars($user['email']); ?></p>
                    </div>

                    <!-- User ID -->
                    <div class="info-card">
                        <div class="info-card-header">
                            <div class="info-icon">
                                <i class="fas fa-user"></i>
                            </div>
                            <h3>User ID</h3>
                        </div>
                        <p><?php echo htmlspecialchars($user['id']); ?></p>
                    </div>

                    <!-- Account Type -->
                    <div class="info-card">
                        <div class="info-card-header">
                            <div class="info-icon">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <h3>Account Type</h3>
                        </div>
                        <p><?php echo $is_admin ? 'Admin (Barangay Official)' : 'Regular User (Citizen)'; ?></p>
                    </div>

                    <!-- Member Since -->
                    <div class="info-card">
                        <div class="info-card-header">
                            <div class="info-icon">
                                <i class="fas fa-calendar"></i>
                            </div>
                            <h3>Member Since</h3>
                        </div>
                        <p><?php echo $join_date; ?></p>
                    </div>

                    <?php if (isset($user['barangay']) && !empty($user['barangay'])): ?>
                    <!-- Barangay -->
                    <div class="info-card">
                        <div class="info-card-header">
                            <div class="info-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <h3>Barangay</h3>
                        </div>
                        <p><?php echo htmlspecialchars($user['barangay']); ?></p>
                    </div>
                    <?php endif; ?>

                    <?php if (isset($user['id_type']) && !empty($user['id_type'])): ?>
                    <!-- Valid ID Type -->
                    <div class="info-card">
                        <div class="info-card-header">
                            <div class="info-icon">
                                <i class="fas fa-id-card"></i>
                            </div>
                            <h3>Valid ID Type</h3>
                        </div>
                        <p class="capitalize"><?php echo htmlspecialchars(str_replace('-', ' ', $user['id_type'])); ?></p>
                    </div>
                    <?php endif; ?>

                    <?php if (isset($user['valid_id']) && !empty($user['valid_id'])): ?>
                    <!-- Valid ID Document -->
                    <div class="info-card" style="grid-column: 1 / -1;">
                        <div class="info-card-header">
                            <div class="info-icon">
                                <i class="fas fa-id-card"></i>
                            </div>
                            <h3>Valid ID Document</h3>
                        </div>
                        <div class="id-image">
                            <img 
                                src="<?php echo htmlspecialchars($user['valid_id']); ?>" 
                                alt="Valid ID"
                            />
                            <p>ID verified during registration</p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Account Status -->
                <div class="account-status">
                    <h3>Account Status</h3>
                    <div class="status-indicator">
                        <span class="status-dot"></span>
                        <span class="status-text">Active and in good standing</span>
                    </div>
                </div>

                <!-- Additional Info for Admin -->
                <?php if ($is_admin): ?>
                <div class="admin-info">
                    <h3>Admin Access</h3>
                    <p>
                        You have administrative privileges to manage health requests, 
                        update request statuses, and send notifications to citizens.
                    </p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <?php if (!$is_admin): ?>
            <a href="submitreq.php" class="action-card">
                <div class="action-icon">📝</div>
                <h3>Submit Request</h3>
                <p>Create a new health request</p>
            </a>

            <a href="trackreq.php" class="action-card">
                <div class="action-icon">🔍</div>
                <h3>Track Request</h3>
                <p>Check your request status</p>
            </a>
            <?php else: ?>
            <a href="admin-dashboard.php" class="action-card">
                <div class="action-icon">📊</div>
                <h3>Admin Dashboard</h3>
                <p>Manage all health requests</p>
            </a>
            <?php endif; ?>

            <form method="POST" style="display: contents;">
                <button type="submit" name="logout" class="action-card logout" style="text-align: center;">
                    <div class="action-icon">🚪</div>
                    <h3>Logout</h3>
                    <p>Sign out of your account</p>
                </button>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="footer-container">
            <div class="footer-grid">
                <!-- Barangay Health Office -->
                <div class="footer-section">
                    <h3>🏢 Barangay Health Office</h3>
                    <div class="footer-content">
                        <div class="footer-item">
                            <div class="footer-label">📍 Address</div>
                            <div class="footer-value">Deparo, Caloocan City, Metro Manila</div>
                        </div>
                        <div class="footer-item">
                            <div class="footer-label">📞 Hotline</div>
                            <div class="footer-value">(02) 8123-4567</div>
                        </div>
                        <div class="footer-item">
                            <div class="footer-label">📧 Email</div>
                            <div class="footer-value">K1contrerascris@gmail.com</div>
                        </div>
                        <div class="footer-item">
                            <div class="footer-label">🕐 Office Hours</div>
                            <div class="footer-value">Mon-Fri, 8:00 AM - 5:00 PM</div>
                        </div>
                    </div>
                </div>

                <!-- Emergency Hotlines -->
                <div class="footer-section">
                    <h3>📞 Emergency Hotlines</h3>
                    <div class="footer-content">
                        <div class="emergency-item">
                            <span>Police</span>
                            <span>(02) 8426-4663</span>
                        </div>
                        <div class="emergency-item">
                            <span>BFP</span>
                            <span>(02) 8245 0849</span>
                        </div>
                    </div>
                </div>

                <!-- Hospitals Near Barangay -->
                <div class="footer-section">
                    <h3>🏥 Hospitals Near Barangay</h3>
                    <div class="footer-content">
                        <div class="hospital-item">
                            <div class="hospital-name">Camarin Doctors Hospital</div>
                            <div class="hospital-phone">(02) 2-7004-2881</div>
                        </div>
                        <div class="hospital-item">
                            <div class="hospital-name">Caloocan City North Medical</div>
                            <div class="hospital-phone">(02) 8288 7077</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Copyright -->
            <div class="footer-copyright">
                <p>© 2025 Barangay 170, Deparo, Caloocan. All rights reserved.</p>
                <p>Barangay Citizen Document Request System (BCDRS)</p>
            </div>
        </div>
    </footer>
</body>
</html>