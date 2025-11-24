<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: sign-in.php");
    exit();
}

// Handle logout from header
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

$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['logout'])) {
    $requesttype = $_POST['requesttype'];
    $description = trim($_POST['description']);
    $contact = trim($_POST['contact']);
    $user_id = $_SESSION['user_id'];

    // Validation
    if (empty($requesttype) || empty($description) || empty($contact)) {
        $error = "All fields are required.";
    } elseif (!preg_match('/^[0-9]{10,11}$/', $contact)) {
        $error = "Please enter a valid contact number (10-11 digits).";
    } else {
        // Generate unique ticket ID
$year = date('Y');

// Get the highest ticket number for this year
$stmt = $conn->prepare("SELECT ticket_id FROM requests WHERE YEAR(submitted_at) = ? ORDER BY id DESC LIMIT 1");
$stmt->bind_param("i", $year);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $last_ticket = $row['ticket_id'];
    // Extract the number part (e.g., "BHR-2025-000004" -> "000004")
    $last_number = intval(substr($last_ticket, -6));
    $next_number = $last_number + 1;
} else {
    // No tickets for this year yet, start from 1
    $next_number = 1;
}

$ticket_id = 'BHR-' . $year . '-' . str_pad($next_number, 6, '0', STR_PAD_LEFT);
$stmt->close();

// Double-check if ticket_id exists (safety measure)
$check_stmt = $conn->prepare("SELECT id FROM requests WHERE ticket_id = ?");
$check_stmt->bind_param("s", $ticket_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

// If ticket exists, keep incrementing until we find a unique one
while ($check_result->num_rows > 0) {
    $next_number++;
    $ticket_id = 'BHR-' . $year . '-' . str_pad($next_number, 6, '0', STR_PAD_LEFT);
    $check_stmt->bind_param("s", $ticket_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
}
$check_stmt->close();

        // Get user email for the name field
        $stmt = $conn->prepare("SELECT email FROM account WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($user_email);
        $stmt->fetch();
        $stmt->close();

        // Insert request
        $stmt = $conn->prepare("INSERT INTO requests (ticket_id, user_id, fullname, contact, requesttype, description) VALUES (?, ?, ?, ?, ?, ?)");
        $fullname = $user_email;
        $stmt->bind_param("sissss", $ticket_id, $user_id, $fullname, $contact, $requesttype, $description);
        if ($stmt->execute()) {
            $success = "Request submitted successfully! Your Ticket ID is: <strong>$ticket_id</strong>. Use it to track your request.";
        } else {
            $error = "Error submitting request. Please try again.";
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Submit Request — eBCsH</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    * { 
      margin: 0; 
      padding: 0; 
      box-sizing: border-box; 
      font-family: 'Poppins', sans-serif; 
    }
    
    body { 
      min-height: 100vh;
      background: #DAF1DE;
      color: #333; 
    }

    /* Header */
    header { 
      background: white;
      display: flex; 
      justify-content: space-between; 
      align-items: center; 
      padding: 1rem 2rem; 
      box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    
    .header-left { 
      display: flex; 
      align-items: center;
      cursor: pointer;
      background: transparent;
      border: none;
      text-decoration: none;
      color: inherit;
      transition: opacity 0.3s;
    }

    .header-left:hover {
      opacity: 0.8;
    }
    
    .header-left img { 
      height: 50px;
      width: 50px;
      margin-right: 10px;
      border-radius: 50%;
    }
    
    .header-title-wrap {
      text-align: left;
    }
    
    .header-title-wrap .title { 
      font-size: 16px; 
      font-weight: 500;
      color: #000;
    }
    
    .header-title-wrap .subtitle {
      font-size: 14px;
      opacity: 0.7;
    }

    .header-right {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .profile-btn {
      background: #16a34a;
      color: white;
      border: none;
      padding: 10px;
      border-radius: 6px;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: background 0.3s;
      text-decoration: none;
    }

    .profile-btn:hover {
      background: #15803d;
    }
    
    .logout-btn { 
      background: #FD7E7E;
      color: #fff; 
      border: none; 
      padding: 10px 20px; 
      border-radius: 6px; 
      font-weight: 600;
      font-size: 14px;
      cursor: pointer;
      transition: background 0.3s;
    }
    
    .logout-btn:hover {
      background: #fc6b6b;
    }
    
    .logout-btn i { 
      margin-right: 6px; 
    }

    /* Main Content */
    .main-container {
      max-width: 1000px;
      margin: 28px auto;
      padding: 0 16px;
    }

    .form-container { 
      max-width: 700px;
      margin: 20px auto;
      background: white;
      padding: 32px; 
      border-radius: 16px; 
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .form-title { 
      font-size: 28px; 
      font-weight: 600; 
      color: #2e7d3a; 
      margin-bottom: 6px; 
    }
    
    .form-description { 
      color: #556; 
      margin-bottom: 20px;
      line-height: 1.6;
      font-size: 15px;
    }
    
    form { 
      display: flex; 
      flex-direction: column; 
      gap: 20px; 
    }
    
    .form-group { 
      display: flex; 
      flex-direction: column; 
    }
    
    .form-label { 
      font-weight: 500; 
      color: #249c3b; 
      margin-bottom: 6px;
      font-size: 14px;
    }
    
    select, textarea, input[type="text"] { 
      padding: 10px; 
      border-radius: 8px; 
      border: 1px solid #e6efe6; 
      background: #fbfffb; 
      outline: none;
      font-size: 14px;
      font-family: 'Poppins', sans-serif;
    }

    select:focus, textarea:focus, input[type="text"]:focus {
      border-color: #249c3b;
    }
    
    textarea { 
      min-height: 120px;
      resize: vertical;
    }

    select {
      cursor: pointer;
    }
    
    .submit-button { 
      background: #00B050;
      color: #fff; 
      padding: 12px; 
      border-radius: 8px; 
      border: none; 
      font-weight: 600;
      font-size: 15px;
      cursor: pointer;
      transition: background 0.3s;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .submit-button:hover {
      background: #009944;
    }
    
    .message { 
      padding: 12px; 
      border-radius: 8px;
      margin-bottom: 20px;
      font-size: 14px;
    }
    
    .success { 
      background: #e6f8ec; 
      color: #1b6b2b;
    }
    
    .error { 
      background: #fff0f0; 
      color: #942020;
    }

    /* Footer */
    footer {
      background: white;
      border-top: 1px solid #dcfce7;
      margin-top: 48px;
    }

    .footer-content {
      max-width: 1000px;
      margin: 0 auto;
      padding: 32px 24px;
    }

    .footer-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 32px;
      margin-bottom: 24px;
    }

    .footer-section {
      text-align: center;
    }

    .footer-section h3 {
      font-size: 18px;
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
      font-size: 15px;
    }

    .footer-item-label {
      color: #15803d;
      font-weight: 500;
      margin-bottom: 4px;
    }

    .footer-item-value {
      color: #166534;
      font-size: 14px;
    }

    .footer-hospital {
      margin-bottom: 12px;
    }

    .footer-hospital-name {
      color: #15803d;
      font-weight: 500;
    }

    .footer-hospital-phone {
      color: #166534;
      font-size: 14px;
    }

    .footer-copyright {
      border-top: 1px solid #dcfce7;
      padding-top: 24px;
      text-align: center;
      color: #15803d;
      font-size: 15px;
    }

    .footer-copyright p {
      margin-bottom: 8px;
    }

    @media (max-width: 768px) { 
      header {
        padding: 12px 16px;
      }

      .header-left img {
        height: 40px;
        width: 40px;
      }

      .main-container {
        padding: 0 12px;
      }

      .form-container {
        padding: 24px;
      }

      .form-title {
        font-size: 24px;
      }

      .footer-grid {
        grid-template-columns: 1fr;
        gap: 24px;
      }
    }
  </style>
</head>
<body>
  <header>
    <a href="homepage.php" class="header-left">
      <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRTDCuh4kIpAtR-QmjA1kTjE_8-HSd8LSt3Gw&s" alt="Logo">
      <div class="header-title-wrap">
        <div class="title">Barangay 170</div>
        <div class="subtitle">Community Portal</div>
      </div>
    </a>
    <div class="header-right">
      <!-- Profile Button -->
      <a href="profile.php" class="profile-btn">
        <i class="fas fa-user-circle"></i>
      </a>

      <!-- Logout Button -->
      <form method="POST" action="submitreq.php" style="display:inline; margin: 0;">
        <button type="submit" name="logout" class="logout-btn">
          <i class="fas fa-sign-out-alt"></i> Logout
        </button>
      </form>
    </div>
  </header>

  <div class="main-container">
    <div class="form-container">
      <h1 class="form-title">Submit New Request</h1>
      <p class="form-description">
        Please provide detailed information about your request or concern. 
        The barangay will process it and notify you when ready.
      </p>

      <?php if ($success): ?>
        <div class="message success"><?php echo $success; ?></div>
      <?php elseif ($error): ?>
        <div class="message error"><?php echo $error; ?></div>
      <?php endif; ?>

      <form method="POST" action="submitreq.php">
        <div class="form-group">
          <label class="form-label" for="requesttype">Request Type</label>
          <select id="requesttype" name="requesttype" required>
            <option value="" disabled selected>Select the type of request</option>
            <option value="Barangay Blotter / Incident Report Copy">Barangay Blotter / Incident Report Copy</option>
            <option value="Barangay Business Clearance">Barangay Business Clearance</option>
            <option value="Barangay Certificate for Livelihood Program Application">Barangay Certificate for Livelihood Program Application</option>
            <option value="Barangay Certificate for Water/Electric Connection (Proof of Occupancy/Ownership)">Barangay Certificate for Water/Electric Connection (Proof of Occupancy/Ownership)</option>
            <option value="Barangay Certificate of Guardianship">Barangay Certificate of Guardianship</option>
            <option value="Barangay Certificate of Household Membership">Barangay Certificate of Household Membership</option>
            <option value="Barangay Certificate of No Derogatory Record">Barangay Certificate of No Derogatory Record</option>
            <option value="Barangay Certificate of No Objection (CNO)">Barangay Certificate of No Objection (CNO)</option>
            <option value="Barangay Certification for PWD">Barangay Certification for PWD</option>
            <option value="Barangay Certification for Solo Parent (Referral for DSWD)">Barangay Certification for Solo Parent (Referral for DSWD)</option>
            <option value="Barangay Clearance">Barangay Clearance</option>
            <option value="Barangay Clearance for Street Vending">Barangay Clearance for Street Vending</option>
            <option value="Barangay Construction / Renovation Permit">Barangay Construction / Renovation Permit</option>
            <option value="Barangay Endorsement Letter">Barangay Endorsement Letter</option>
            <option value="Barangay Event Permit (Sound Permit, Activity Permit)">Barangay Event Permit (Sound Permit, Activity Permit)</option>
            <option value="Barangay ID">Barangay ID</option>
            <option value="Certificate of Indigency">Certificate of Indigency</option>
            <option value="Certificate of Residency">Certificate of Residency</option>
            <option value="Clearance of No Objection">Clearance of No Objection</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label" for="contact">Contact Number</label>
          <input type="text" id="contact" name="contact" placeholder="Enter your contact number (e.g., 09171234567)" required pattern="[0-9]{10,11}" title="Please enter a valid 10-11 digit contact number">
        </div>

        <div class="form-group">
          <label class="form-label" for="description">Detailed Description</label>
          <textarea id="description" name="description" placeholder="Provide details, symptoms, timeline, or assistance needed." required></textarea>
        </div>

        <button type="submit" class="submit-button">Submit</button>
      </form>
    </div>
  </div>

  <!-- Footer -->
  <footer>
    <div class="footer-content">
      <div class="footer-grid">
        <!-- Barangay Health Office -->
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

        <!-- Emergency Hotlines -->
        <div class="footer-section">
          <h3>📞 Emergency Hotlines</h3>
          <div class="footer-section-content">
            <div class="footer-item">
              <span class="footer-item-label" style="min-width: 80px; display: inline-block;">Police</span>
              <span class="footer-item-value">(02) 8426-4663</span>
            </div>
            <div class="footer-item">
              <span class="footer-item-label" style="min-width: 80px; display: inline-block;">BFP</span>
              <span class="footer-item-value">(02) 8245 0849</span>
            </div>
          </div>
        </div>

        <!-- Hospitals Near Barangay -->
        <div class="footer-section">
          <h3>🏥 Hospitals Near Barangay</h3>
          <div class="footer-section-content">
            <div class="footer-hospital">
              <div class="footer-hospital-name">Camarin Doctors Hospital</div>
              <div class="footer-hospital-phone">(02) 2-7004-2881</div>
            </div>
            <div class="footer-hospital">
              <div class="footer-hospital-name">Caloocan City North Medical</div>
              <div class="footer-hospital-phone">(02) 8288 7077</div>
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