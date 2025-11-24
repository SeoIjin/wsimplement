<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: sign-in.php");
    exit();
}
// Handle logout
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['logout'])) {
    session_destroy();
    header("Location: sign-in.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>BCDRS - Community Portal</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }
    
    body {
      background: linear-gradient(135deg, #d1fae5 0%, #ecfdf5 50%, #ccfbf1 100%);
      min-height: 100vh;
      color: #333;
    }
    
    /* Header */
    header {
      background-color: #fff;
      border-bottom: 1px solid #e5e7eb;
      padding: 1rem 1.5rem;
    }
    
    .header-container {
      max-width: 1600px;
      margin: 0 auto;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .header-left {
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }
    
    .header-left img {
      width: 40px;
      height: 40px;
    }
    
    .header-left .title h1 {
      font-size: 1.125rem;
      color: #166534;
      font-weight: 600;
      margin: 0;
    }
    
    .header-left .title p {
      font-size: 0.875rem;
      color: #16a34a;
      margin: 0;
    }

    .header-right {
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }

    .notification-wrapper {
      position: relative;
    }

    .notification-btn,
    .profile-btn {
      background-color: #16a34a;
      color: #fff;
      border: none;
      padding: 0.625rem;
      border-radius: 0.375rem;
      cursor: pointer;
      transition: background-color 0.2s;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
    }

    .notification-btn:hover,
    .profile-btn:hover {
      background-color: #15803d;
    }

    .notification-badge {
      position: absolute;
      top: -4px;
      right: -4px;
      background: #ef4444;
      color: #fff;
      border-radius: 50%;
      width: 18px;
      height: 18px;
      display: none;
      align-items: center;
      justify-content: center;
      font-size: 0.625rem;
      font-weight: 600;
    }

    .notification-dropdown {
      display: none;
      position: absolute;
      top: calc(100% + 0.5rem);
      right: 0;
      background: white;
      border: 1px solid #e5e7eb;
      border-radius: 0.5rem;
      box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
      width: 400px;
      max-height: 500px;
      overflow-y: auto;
      z-index: 1000;
    }

    .notification-dropdown.show {
      display: block;
    }

    .notification-header {
      padding: 1rem;
      border-bottom: 1px solid #e5e7eb;
      position: sticky;
      top: 0;
      background: white;
      z-index: 1;
    }

    .notification-header h3 {
      margin: 0;
      font-size: 1rem;
      font-weight: 600;
      color: #14532d;
    }

    .urgent-section {
      border-bottom: 2px solid #fbbf24;
    }

    .urgent-header {
      padding: 0.75rem 1rem;
      background: #fef3c7;
      border-bottom: 1px solid #fbbf24;
    }

    .urgent-header h4 {
      margin: 0;
      font-size: 0.875rem;
      font-weight: 600;
      color: #92400e;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .urgent-item {
      padding: 1rem;
      background: #fffbeb;
      border-bottom: 1px solid #fef3c7;
      cursor: pointer;
      transition: background-color 0.2s;
    }

    .urgent-item:hover {
      background: #fef3c7;
    }

    .urgent-badge {
      background: #f59e0b;
      color: white;
      padding: 0.125rem 0.375rem;
      border-radius: 0.25rem;
      font-size: 0.6875rem;
      font-weight: 600;
    }

    .notification-empty {
      padding: 2rem;
      text-align: center;
      color: #6b7280;
    }

    .notification-item {
      padding: 1rem;
      border-bottom: 1px solid #f3f4f6;
      cursor: pointer;
      transition: background-color 0.2s;
    }

    .notification-item:hover {
      background: #f9fafb;
    }

    .status-badge {
      display: inline-block;
      padding: 0.125rem 0.375rem;
      border-radius: 0.25rem;
      font-size: 0.6875rem;
      font-weight: 600;
      color: white;
    }

    .notification-footer {
      padding: 0.75rem 1rem;
      border-top: 1px solid #e5e7eb;
      background: #f9fafb;
    }

    .notification-footer button {
      width: 100%;
      background: #16a34a;
      color: white;
      border: none;
      padding: 0.5rem;
      border-radius: 0.375rem;
      font-size: 0.875rem;
      font-weight: 600;
      cursor: pointer;
      transition: background-color 0.2s;
    }

    .notification-footer button:hover {
      background: #15803d;
    }
    
    .logout-btn {
      background-color: #ef4444;
      color: #fff;
      border: none;
      padding: 0.625rem 1.25rem;
      border-radius: 0.375rem;
      font-weight: 600;
      cursor: pointer;
      transition: background-color 0.2s;
    }
    
    .logout-btn:hover {
      background-color: #dc2626;
    }
    
    /* Main Layout */
    .main-wrapper {
      max-width: 1600px;
      margin: 0 auto;
      padding: 3rem 1.5rem;
      display: flex;
      gap: 2rem;
      align-items: start;
      justify-content: center;
    }
    
    /* Sidebars */
    .sidebar {
      width: 320px;
      flex-shrink: 0;
    }
    
    .sidebar-card {
      background: rgba(255, 255, 255, 0.9);
      backdrop-filter: blur(8px);
      border: 1px solid #d1fae5;
      border-radius: 0.5rem;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
      position: sticky;
      top: 6rem;
    }
    
    .card-header {
      padding: 1.25rem 1.5rem 0.5rem;
      border-bottom: 1px solid #dcfce7;
    }
    
    .card-title {
      font-size: 1.125rem;
      font-weight: 700;
      color: #14532d;
      display: flex;
      align-items: center;
      gap: 0.5rem;
      margin: 0;
    }
    
    .card-content {
      padding: 1.5rem;
    }
    
    /* Updates Sidebar */
    .update-item {
      margin-bottom: 1.5rem;
      padding-bottom: 1.5rem;
      border-bottom: 1px solid #dcfce7;
    }
    
    .update-item:last-child {
      border-bottom: none;
      padding-bottom: 0;
    }
    
    .update-header {
      display: flex;
      gap: 0.5rem;
      align-items: start;
      margin-bottom: 0.5rem;
    }
    
    .badge {
      padding: 0.25rem 0.5rem;
      border-radius: 0.25rem;
      font-size: 0.75rem;
      font-weight: 600;
      flex-shrink: 0;
    }
    
    .badge-news {
      background-color: #dcfce7;
      color: #15803d;
    }
    
    .badge-event {
      background-color: #ffedd5;
      color: #ea580c;
    }
    
    .update-title {
      font-size: 0.9375rem;
      color: #14532d;
      font-weight: 600;
      margin-bottom: 0.25rem;
    }
    
    .update-date {
      font-size: 0.8125rem;
      color: #15803d;
      margin-bottom: 0.5rem;
    }
    
    .update-description {
      font-size: 0.875rem;
      color: #16a34a;
      line-height: 1.5;
      margin: 0;
    }

    .show-more-btn {
      background: none;
      color: #16a34a;
      border: none;
      padding: 0.5rem 0;
      text-align: center;
      cursor: pointer;
      transition: color 0.2s;
      font-family: 'Poppins', sans-serif;
      font-size: 0.875rem;
      width: 100%;
      font-weight: 500;
    }

    .show-more-btn:hover {
      color: #15803d;
    }
    
    /* Center Content */
    .center-content {
      flex: 1;
      max-width: 1000px;
    }
    
    /* Welcome Section */
    .welcome-section {
      text-align: center;
      margin-bottom: 2rem;
    }
    
    .logo-circle {
      display: inline-block;
      padding: 1rem;
      background-color: #fff;
      border-radius: 50%;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      margin-bottom: 1rem;
    }
    
    .logo-circle img {
      width: 48px;
      height: 48px;
    }
    
    .welcome-section h1 {
      font-size: 1.875rem;
      color: #14532d;
      font-weight: 700;
      margin-bottom: 0.75rem;
    }
    
    .welcome-section p {
      font-size: 1rem;
      color: #15803d;
      max-width: 42rem;
      margin: 0 auto;
      line-height: 1.6;
    }
    
    /* Action Cards */
    .action-cards {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 1.5rem;
      margin-bottom: 2rem;
    }
    
    .action-card {
      background-color: #fff;
      border: 1px solid #dcfce7;
      border-radius: 0.5rem;
      padding: 2rem 1.5rem;
      text-align: center;
      transition: box-shadow 0.3s;
      cursor: pointer;
      display: flex;
      flex-direction: column;
      height: 100%;
    }
    
    .action-card:hover {
      box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
    }
    
    .action-icon {
      width: 64px;
      height: 64px;
      background-color: #dcfce7;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 1rem;
      font-size: 2rem;
    }
    
    .action-card h3 {
      font-size: 1.25rem;
      color: #14532d;
      font-weight: 700;
      margin-bottom: 0.5rem;
    }
    
    .action-card p {
      font-size: 0.9375rem;
      color: #16a34a;
      margin-bottom: 1.25rem;
      line-height: 1.5;
      flex-grow: 1;
    }
    
    .action-card button {
      border: none;
      padding: 0.625rem 1.25rem;
      border-radius: 0.375rem;
      font-weight: 600;
      font-size: 0.9375rem;
      cursor: pointer;
      color: #fff;
      transition: background-color 0.2s;
    }
    
    .btn-submit {
      background-color: #3b82f6;
    }
    
    .btn-submit:hover {
      background-color: #2563eb;
    }
    
    .btn-track {
      background-color: #16a34a;
    }
    
    .btn-track:hover {
      background-color: #15803d;
    }
    
    /* How It Works */
    .how-it-works {
      background-color: #fff;
      border: 1px solid #dcfce7;
      border-radius: 0.5rem;
      padding: 2rem 1.5rem;
    }
    
    .how-it-works h2 {
      text-align: center;
      font-size: 1.5rem;
      color: #14532d;
      font-weight: 700;
      margin-bottom: 2rem;
    }
    
    .steps {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 2rem;
    }
    
    .step {
      text-align: center;
    }
    
    .step-icon {
      width: 64px;
      height: 64px;
      background-color: #ffedd5;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 0.75rem;
      font-size: 2rem;
      color: #ea580c;
    }
    
    .step h3 {
      font-size: 1.125rem;
      color: #14532d;
      font-weight: 600;
      margin-bottom: 0.75rem;
    }
    
    .step p {
      font-size: 0.9375rem;
      color: #15803d;
      line-height: 1.6;
      margin: 0;
    }
    
    /* Footer */
    footer {
      background-color: #fff;
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

    .footer-section-content {
      display: inline-block;
      text-align: left;
    }

    .footer-item {
      margin-bottom: 0.75rem;
      font-size: 0.9375rem;
    }

    .footer-item-label {
      color: #15803d;
      font-weight: 500;
      margin-bottom: 0.25rem;
    }

    .footer-item-value {
      color: #166534;
      font-size: 0.875rem;
    }

    .footer-hospital {
      margin-bottom: 0.75rem;
    }

    .footer-hospital-name {
      color: #15803d;
      font-weight: 500;
    }

    .footer-hospital-phone {
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
    @media (max-width: 1280px) {
      .sidebar {
        display: none;
      }
      
      .main-wrapper {
        justify-content: center;
      }
    }
    
    @media (max-width: 768px) {
      .action-cards {
        grid-template-columns: 1fr;
      }
      
      .steps {
        grid-template-columns: 1fr;
        gap: 1.5rem;
      }
      
      .footer-grid {
        grid-template-columns: 1fr;
      }
      
      .main-wrapper {
        padding: 2rem 1rem;
      }

      .notification-dropdown {
        width: calc(100vw - 32px);
        right: -140px;
      }
    }
  </style>
</head>
<body>
  <!-- Header -->
  <header>
    <div class="header-container">
      <div class="header-left">
        <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRTDCuh4kIpAtR-QmjA1kTjE_8-HSd8LSt3Gw&s" alt="Barangay Logo">
        <div class="title">
          <h1>Barangay 170</h1>
          <p>Deparo, Caloocan</p>
        </div>
      </div>
      <div class="header-right">
        <!-- Notification Bell -->
        <div class="notification-wrapper">
          <button class="notification-btn" onclick="toggleNotifications()">
            <i class="fas fa-bell"></i>
            <span class="notification-badge" id="notificationBadge">0</span>
          </button>
          
          <!-- Notification Dropdown -->
          <div class="notification-dropdown" id="notificationDropdown">
            <div class="notification-header">
              <h3>Your Notifications</h3>
            </div>
            <div id="notificationContent">
              <div class="notification-empty">
                <p>No requests yet. Submit your first request!</p>
              </div>
            </div>
            <div class="notification-footer">
              <button onclick="window.location.href='trackreq.php'">View All Requests</button>
            </div>
          </div>
        </div>

        <!-- Profile Button -->
        <button class="profile-btn" onclick="window.location.href='profile.php'">
          <i class="fas fa-user-circle"></i>
        </button>

        <form method="POST" action="homepage.php" style="display: inline; margin: 0;">
          <button type="submit" name="logout" class="logout-btn">Logout</button>
        </form>
      </div>
    </div>
  </header>

  <!-- Main Content -->
  <div class="main-wrapper">
    <!-- Right Sidebar - Latest Updates -->
    <aside class="sidebar">
      <div class="sidebar-card">
        <div class="card-header">
          <h2 class="card-title">
            🔔 Latest Updates
          </h2>
        </div>
        <div class="card-content" id="updatesContent">
          <!-- Updates will be loaded here -->
        </div>
      </div>
    </aside>

    <!-- Center Content -->
    <main class="center-content">
      <!-- Welcome Section -->
      <section class="welcome-section">
        <div class="logo-circle">
          <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRTDCuh4kIpAtR-QmjA1kTjE_8-HSd8LSt3Gw&s" alt="eBCsH Logo">
        </div>
        <h1>Welcome to BCDRS</h1>
        <p>Submit health-related requests to your barangay and track their progress in real time. Our system ensures transparency and efficient processing of your requests.</p>
      </section>

      <!-- Action Cards -->
      <div class="action-cards">
        <div class="action-card" onclick="window.location.href='submitreq.php'">
          <div class="action-icon">
            📝
          </div>
          <h3>Submit Request</h3>
          <p>File new requests directly to your local barangay health office</p>
          <div style="display: flex; justify-content: center;">
            <button class="btn-submit">Start a New Request</button>
          </div>
        </div>
        <div class="action-card" onclick="window.location.href='trackreq.php'">
          <div class="action-icon">
            🔍
          </div>
          <h3>Track Request</h3>
          <p>Check the status and updates on your submitted health requests</p>
          <div style="display: flex; justify-content: center;">
            <button class="btn-track">View Existing Request</button>
          </div>
        </div>
      </div>

      <!-- How it Works -->
      <section class="how-it-works">
        <h2>How it Works</h2>
        <div class="steps">
          <div class="step">
            <div class="step-icon">
              ⬆️
            </div>
            <h3>Submit</h3>
            <p>Fill out the request form with your details and submit it to the barangay health office</p>
          </div>
          <div class="step">
            <div class="step-icon">
              🔔
            </div>
            <h3>Track</h3>
            <p>Monitor your request's status in real-time and receive notifications for any updates</p>
          </div>
          <div class="step">
            <div class="step-icon">
              ✅
            </div>
            <h3>Receive</h3>
            <p>Get notified whenever your request is approved and ready for pickup or delivery</p>
          </div>
        </div>
      </section>
    </main>
  </div>

  <!-- Footer -->
  <footer>
    <div class="footer-container">
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
      <div class="footer-copyright">
        <p>© 2025 Barangay 170, Deparo, Caloocan. All rights reserved.</p>
        <p>Barangay Citizen Document Request System (BCDRS)</p>
      </div>
    </div>
  </footer>

  <script>
    let notificationsExpanded = false;

    function toggleNotifications() {
      const dropdown = document.getElementById('notificationDropdown');
      dropdown.classList.toggle('show');
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
      const wrapper = document.querySelector('.notification-wrapper');
      const dropdown = document.getElementById('notificationDropdown');
      
      if (!wrapper.contains(event.target)) {
        dropdown.classList.remove('show');
      }
    });

    // Fetch user requests and display in notification dropdown
    async function fetchUserRequests() {
      try {
        const response = await fetch('api_get_user_requests.php', {
          cache: 'no-store'
      });
    
      if (!response.ok) {
        console.error('Failed to fetch user requests');
      return;
      }
    
      const data = await response.json();
      console.log('📊 Notification Data:', data); // Debug log
    
    if (data.requests && data.requests.length > 0) {
        // Count total notifications
        let totalNotifications = 0;
      
      // Count updates from requests
      data.requests.forEach(request => {
        if (request.updates && request.updates.length > 0) {
          totalNotifications += request.updates.length;
        }
      });
      
      // Add urgent notifications count
      const urgentNotifications = data.notifications ? 
        data.notifications.filter(n => n.type === 'REQUEST_UPDATE' || n.type === 'STATUS_CHANGE' || n.type === 'PRIORITY_CHANGE') : [];
      
      totalNotifications += urgentNotifications.length;
      
      // Show badge with count
      const badge = document.getElementById('notificationBadge');
      if (totalNotifications > 0) {
        badge.textContent = totalNotifications;
        badge.style.display = 'flex';
      } else {
        badge.style.display = 'none';
      }
      
      // Get all updates from requests
      const allUpdates = [];
      data.requests.forEach(request => {
        if (request.updates && request.updates.length > 0) {
          request.updates.forEach(update => {
            allUpdates.push({
              ...update,
              requestType: request.type,
              ticketId: request.ticket_id,
              requestId: request.id,
              isUrgent: false
            });
          });
        }
      });
      
      // Sort by timestamp (newest first)
      allUpdates.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
      
      // Display notifications
      const content = document.getElementById('notificationContent');
      let html = '';
      
      // Display urgent notifications first (from audit trail)
      if (urgentNotifications.length > 0) {
        html += '<div class="urgent-section">';
        urgentNotifications.forEach(notification => {
          html += `
            <div class="urgent-item" onclick="window.location.href='trackreq.php'">
              <div style="display: flex; align-items: start; gap: 0.5rem; margin-bottom: 0.5rem;">
                <span style="font-size: 0.6875rem; color: #92400e;">${notification.date}</span>
              </div>
              <div style="font-size: 0.875rem; font-weight: 600; color: #92400e; margin-bottom: 0.25rem;">
                ${notification.title}
              </div>
              ${notification.ticketId ? `
                <div style="font-size: 0.75rem; color: #78350f; margin-bottom: 0.5rem;">
                  Ticket: ${notification.ticketId}
                </div>
              ` : ''}
              <p style="font-size: 0.75rem; color: #78350f; margin: 0; line-height: 1.4;">
                ${notification.description}
              </p>
              <div style="margin-top: 0.5rem; font-size: 0.6875rem; color: #92400e; font-weight: 600;">
                👉 Click to view request details
              </div>
            </div>
          `;
        });
        html += '</div>';
      }
      
      // Display regular updates
      if (allUpdates.length > 0) {
        // Show latest 5 updates
        const displayUpdates = allUpdates.slice(0, 5);
        
        displayUpdates.forEach(update => {
          html += `
            <div class="notification-item" onclick="window.location.href='requestdetails.php?id=${update.requestId}'">
              <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem;">
                <div style="flex: 1;">
                  <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                    <span class="status-badge" style="background: ${getStatusColor(update.status)};">
                      ${update.status}
                    </span>
                    <span style="font-size: 0.6875rem; color: #9ca3af;">
                      ${formatTimestamp(update.created_at)}
                    </span>
                  </div>
                  <div style="font-size: 0.8125rem; font-weight: 600; color: #14532d; margin-bottom: 0.25rem;">
                    ${update.requestType}
                  </div>
                  <div style="font-size: 0.6875rem; color: #6b7280; margin-bottom: 0.375rem;">
                    ${update.ticketId}
                  </div>
                </div>
              </div>
              <p style="font-size: 0.75rem; color: #4b5563; margin: 0; line-height: 1.4;">
                ${update.message || 'Status updated'}
              </p>
              <div style="font-size: 0.6875rem; color: #9ca3af; margin-top: 0.5rem; font-style: italic;">
                Updated by: ${update.updated_by}
              </div>
            </div>
          `;
        });
      }
    
      if (html === '') {
        html = '<div class="notification-empty"><p>No updates yet. Check back later!</p></div>';
      }
      
      content.innerHTML = html;
      
    } else {
      const badge = document.getElementById('notificationBadge');
      badge.style.display = 'none';
      
      const content = document.getElementById('notificationContent');
      content.innerHTML = '<div class="notification-empty"><p>No requests yet. Submit your first request!</p></div>';
      }
    } catch (error) {
      console.error('❌ Error fetching user requests:', error);
      const content = document.getElementById('notificationContent');
      content.innerHTML = '<div class="notification-empty"><p style="color: #ef4444;">Failed to load notifications. Please refresh.</p></div>';
      }
    }

    function getStatusColor(status) {
    const colors = {
      'PENDING': '#3b82f6',
      'UNDER REVIEW': '#f59e0b',
      'IN PROGRESS': '#8b5cf6',
      'READY': '#10b981',
      'COMPLETED': '#059669',
      'New': '#3b82f6'
    };
      return colors[status] || '#6b7280';
    }

    function formatTimestamp(timestamp) {
    const date = new Date(timestamp);
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);

    if (diffMins < 1) {
      return 'Just now';
    } else if (diffMins < 60) {
      return `${diffMins} min${diffMins > 1 ? 's' : ''} ago`;
    } else if (diffHours < 24) {
      return `${diffHours} hour${diffHours > 1 ? 's' : ''} ago`;
    } else if (diffDays < 7) {
      return `${diffDays} day${diffDays > 1 ? 's' : ''} ago`;
    } else {
      return date.toLocaleDateString('en-US', { 
        month: 'short', 
        day: 'numeric', 
        year: date.getFullYear() !== now.getFullYear() ? 'numeric' : undefined 
      });
      }
    }

    // Fetch updates/notifications for sidebar
    async function fetchUpdates() {
      try {
        const response = await fetch('api_get_notifications.php', {
          cache: 'no-store'
        });
        
        if (!response.ok) {
          console.error('Failed to fetch notifications');
          return;
        }
        
        const notifications = await response.json();
        
        if (notifications && notifications.length > 0) {
          displayUpdates(notifications);
        }
      } catch (error) {
        console.error('Error fetching notifications:', error);
      }
    }

    function displayUpdates(notifications) {
      const content = document.getElementById('updatesContent');
      
      // Filter only NEWS and EVENT types for sidebar
      const sidebarNotifications = notifications.filter(n => n.type === 'NEWS' || n.type === 'EVENT');
      
      const displayCount = notificationsExpanded ? sidebarNotifications.length : Math.min(3, sidebarNotifications.length);
      const displayedNotifications = sidebarNotifications.slice(0, displayCount);
      
      let html = displayedNotifications.map(notification => {
        const badgeClass = notification.type === 'NEWS' ? 'badge-news' : 'badge-event';
        return `
          <div class="update-item">
            <div class="update-header">
              <span class="badge ${badgeClass}">${notification.type}</span>
              <div style="flex: 1;">
                <div class="update-title">${notification.title}</div>
                <div class="update-date">${notification.date}</div>
                <p class="update-description">${notification.description}</p>
              </div>
            </div>
          </div>
        `;
      }).join('');
      
      if (sidebarNotifications.length > 3) {
        html += `
          <button class="show-more-btn" onclick="toggleSidebarNotifications()">
            ${notificationsExpanded ? 'Show Less' : 'Show More'}
          </button>
        `;
      }
      
      content.innerHTML = html || '<div class="notification-empty"><p>No updates available.</p></div>';
    }

    function toggleSidebarNotifications() {
      notificationsExpanded = !notificationsExpanded;
      fetchUpdates();
    }

    // Initial load
    document.addEventListener('DOMContentLoaded', () => {
      fetchUserRequests();
      fetchUpdates();
      
      // Refresh every 30 seconds
      setInterval(() => {
        fetchUserRequests();
        fetchUpdates();
      }, 30000);
    });
  </script>
</body>
</html>