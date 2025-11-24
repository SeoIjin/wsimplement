<?php
session_start();
// require admin session
if (!isset($_SESSION['user_id']) || empty($_SESSION['is_admin'])) {
    header('Location: sign-in.php');
    exit();
}

// Get user ID from URL
$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($user_id <= 0) {
    header('Location: admindashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
  <title>User Profile - Barangay 170</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background: #DAF1DE;
      min-height: 100vh;
    }

    /* Header */
    .page-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 1rem;
      background: white;
      padding: 0.625rem 1.25rem;
      box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .page-header .logo-section {
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }

    .page-header img {
      height: 50px;
      border-radius: 50%;
    }

    .page-header .title-section div:first-child {
      font-weight: 500;
      font-size: 1rem;
    }

    .page-header .title-section div:last-child {
      font-size: 0.875rem;
      color: #666;
    }

    .header-actions {
      display: flex;
      gap: 0.5rem;
      align-items: center;
    }

    .header-actions .btn {
      padding: 0.375rem 1rem;
      font-size: 0.875rem;
      cursor: pointer;
      border: none;
      border-radius: 0.375rem;
      background: #228650;
      color: white;
      transition: opacity 0.2s;
      font-family: 'Poppins', sans-serif;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
    }

    .header-actions .btn:hover {
      opacity: 0.9;
    }

    .header-actions .btn.back {
      background: #6b7280;
    }

    /* Main Content */
    .main-content {
      padding: 2rem;
      max-width: 1400px;
      margin: 0 auto;
    }

    /* Loading State */
    .loading {
      text-align: center;
      padding: 3rem;
      color: #666;
    }

    .spinner {
      border: 3px solid #f3f3f3;
      border-top: 3px solid #228650;
      border-radius: 50%;
      width: 40px;
      height: 40px;
      animation: spin 1s linear infinite;
      margin: 0 auto 1rem;
    }

    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

    /* Profile Container */
    .profile-container {
      display: none;
    }

    .profile-container.show {
      display: block;
    }

    /* Profile Header Card */
    .profile-header-card {
      background: white;
      border-radius: 0.75rem;
      padding: 2rem;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
      margin-bottom: 1.5rem;
      display: flex;
      gap: 2rem;
      align-items: start;
    }

    .profile-avatar {
      flex-shrink: 0;
    }

    .avatar-circle {
      width: 120px;
      height: 120px;
      border-radius: 50%;
      background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 3rem;
      font-weight: 600;
      box-shadow: 0 4px 12px rgba(22, 163, 74, 0.3);
    }

    .profile-info {
      flex: 1;
    }

    .profile-name {
      font-size: 2rem;
      font-weight: 600;
      color: #2c3e50;
      margin-bottom: 0.5rem;
    }

    .profile-email {
      color: #7f8c8d;
      font-size: 1rem;
      margin-bottom: 1rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .profile-badges {
      display: flex;
      gap: 0.75rem;
      flex-wrap: wrap;
    }

    .badge {
      padding: 0.375rem 0.75rem;
      border-radius: 0.375rem;
      font-size: 0.875rem;
      font-weight: 500;
      display: inline-flex;
      align-items: center;
      gap: 0.375rem;
    }

    .badge-resident {
      background: #d4f8e8;
      color: #006b3c;
    }

    .badge-non-resident {
      background: #fff3e0;
      color: #9a4500;
    }

    .badge-info {
      background: #e0e7ff;
      color: #4338ca;
    }

    .badge-date {
      background: #f3f4f6;
      color: #4b5563;
    }

    /* Stats Grid */
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 1rem;
      margin-bottom: 1.5rem;
    }

    .stat-card {
      background: white;
      border-radius: 0.75rem;
      padding: 1.5rem;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
      text-align: center;
    }

    .stat-icon {
      width: 48px;
      height: 48px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 0.75rem;
      font-size: 1.5rem;
    }

    .stat-icon.blue {
      background: #dbeafe;
      color: #2563eb;
    }

    .stat-icon.orange {
      background: #fed7aa;
      color: #ea580c;
    }

    .stat-icon.green {
      background: #d4f8e8;
      color: #16a34a;
    }

    .stat-icon.purple {
      background: #e9d5ff;
      color: #9333ea;
    }

    .stat-value {
      font-size: 2rem;
      font-weight: 600;
      color: #2c3e50;
      margin-bottom: 0.25rem;
    }

    .stat-label {
      font-size: 0.875rem;
      color: #7f8c8d;
    }

    /* Content Grid */
    .content-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1.5rem;
    }

    .info-card {
      background: white;
      border-radius: 0.75rem;
      padding: 1.5rem;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }

    .card-title {
      font-size: 1.125rem;
      font-weight: 600;
      color: #2c3e50;
      margin-bottom: 1.25rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .info-row {
      display: flex;
      justify-content: space-between;
      padding: 0.75rem 0;
      border-bottom: 1px solid #f3f4f6;
    }

    .info-row:last-child {
      border-bottom: none;
    }

    .info-label {
      color: #7f8c8d;
      font-size: 0.875rem;
      font-weight: 500;
    }

    .info-value {
      color: #2c3e50;
      font-size: 0.875rem;
      font-weight: 500;
      text-align: right;
    }

    /* Recent Requests */
    .requests-list {
      display: flex;
      flex-direction: column;
      gap: 0.75rem;
    }

    .request-item {
      padding: 1rem;
      background: #f8f9fa;
      border-radius: 0.5rem;
      border-left: 3px solid #228650;
      transition: all 0.2s;
    }

    .request-item:hover {
      background: #f1f5f9;
      transform: translateX(4px);
    }

    .request-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 0.5rem;
    }

    .request-ticket {
      font-weight: 600;
      color: #2c3e50;
      font-size: 0.875rem;
    }

    .request-status {
      padding: 0.25rem 0.5rem;
      border-radius: 0.25rem;
      font-size: 0.75rem;
      font-weight: 500;
    }

    .status-pending {
      background: #fff3e0;
      color: #f39c12;
    }

    .status-completed {
      background: #e6f8ec;
      color: #07A840;
    }

    .status-in-progress {
      background: #ffe8e4;
      color: #ff6b4a;
    }

    .request-details {
      display: flex;
      justify-content: space-between;
      font-size: 0.8125rem;
      color: #7f8c8d;
    }

    .empty-state {
      text-align: center;
      padding: 2rem;
      color: #7f8c8d;
    }

    .empty-state i {
      font-size: 3rem;
      opacity: 0.3;
      margin-bottom: 0.5rem;
    }

    /* ID Document Preview */
    .id-preview {
      margin-top: 1rem;
      padding: 1rem;
      background: #f8f9fa;
      border-radius: 0.5rem;
      text-align: center;
    }

    .id-preview img {
      max-width: 100%;
      max-height: 300px;
      border-radius: 0.375rem;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .id-preview-label {
      font-size: 0.875rem;
      color: #7f8c8d;
      margin-bottom: 0.5rem;
    }

    /* Responsive */
    @media (max-width: 1024px) {
      .content-grid {
        grid-template-columns: 1fr;
      }

      .stats-grid {
        grid-template-columns: repeat(2, 1fr);
      }
    }

    @media (max-width: 768px) {
      .profile-header-card {
        flex-direction: column;
        align-items: center;
        text-align: center;
      }

      .profile-badges {
        justify-content: center;
      }

      .stats-grid {
        grid-template-columns: 1fr;
      }

      .main-content {
        padding: 1rem;
      }
    }
  </style>
</head>
<body>
  <!-- Header -->
  <div class="page-header">
    <div class="logo-section">
      <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRTDCuh4kIpAtR-QmjA1kTjE_8-HSd8LSt3Gw&s" alt="seal">
      <div class="title-section">
        <div>Barangay 170</div>
        <div>User Profile</div>
      </div>
    </div>
    <div class="header-actions">
      <a href="admindashboard.php" class="btn back">
        <i class="fas fa-arrow-left"></i>
        Back to Dashboard
      </a>
    </div>
  </div>

  <div class="main-content">
    <!-- Loading State -->
    <div id="loadingState" class="loading">
      <div class="spinner"></div>
      <p>Loading user profile...</p>
    </div>

    <!-- Profile Container -->
    <div id="profileContainer" class="profile-container">
      <!-- Profile Header -->
      <div class="profile-header-card">
        <div class="profile-avatar">
          <div class="avatar-circle" id="avatarCircle">
            <i class="fas fa-user"></i>
          </div>
        </div>
        <div class="profile-info">
          <h1 class="profile-name" id="userName">Loading...</h1>
          <div class="profile-email">
            <i class="fas fa-envelope"></i>
            <span id="userEmail">loading@email.com</span>
          </div>
          <div class="profile-badges" id="userBadges"></div>
        </div>
      </div>

      <!-- Stats Grid -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon blue">
            <i class="fas fa-file-alt"></i>
          </div>
          <div class="stat-value" id="totalRequests">0</div>
          <div class="stat-label">Total Requests</div>
        </div>
        <div class="stat-card">
          <div class="stat-icon orange">
            <i class="fas fa-clock"></i>
          </div>
          <div class="stat-value" id="pendingRequests">0</div>
          <div class="stat-label">Pending</div>
        </div>
        <div class="stat-card">
          <div class="stat-icon purple">
            <i class="fas fa-spinner"></i>
          </div>
          <div class="stat-value" id="inProgressRequests">0</div>
          <div class="stat-label">In Progress</div>
        </div>
        <div class="stat-card">
          <div class="stat-icon green">
            <i class="fas fa-check-circle"></i>
          </div>
          <div class="stat-value" id="completedRequests">0</div>
          <div class="stat-label">Completed</div>
        </div>
      </div>

      <!-- Content Grid -->
      <div class="content-grid">
        <!-- Personal Information -->
        <div class="info-card">
          <h2 class="card-title">
            <i class="fas fa-user-circle"></i>
            Personal Information
          </h2>
          <div class="info-row">
            <span class="info-label">First Name</span>
            <span class="info-value" id="firstName">-</span>
          </div>
          <div class="info-row">
            <span class="info-label">Middle Name</span>
            <span class="info-value" id="middleName">-</span>
          </div>
          <div class="info-row">
            <span class="info-label">Last Name</span>
            <span class="info-value" id="lastName">-</span>
          </div>
          <div class="info-row">
            <span class="info-label">Email Address</span>
            <span class="info-value" id="emailInfo">-</span>
          </div>
          <div class="info-row">
            <span class="info-label">Barangay</span>
            <span class="info-value" id="barangay">-</span>
          </div>
          <div class="info-row">
            <span class="info-label">Resident Type</span>
            <span class="info-value" id="residentType">-</span>
          </div>
          <div class="info-row">
            <span class="info-label">ID Type</span>
            <span class="info-value" id="idType">-</span>
          </div>
          
          <!-- ID Document Preview -->
          <div class="id-preview" id="idPreview" style="display: none;">
            <div class="id-preview-label">Uploaded ID Document</div>
            <img id="idImage" src="" alt="ID Document">
          </div>
        </div>

        <!-- Recent Requests -->
        <div class="info-card">
          <h2 class="card-title">
            <i class="fas fa-history"></i>
            Recent Requests
          </h2>
          <div class="requests-list" id="recentRequests">
            <div class="empty-state">
              <i class="fas fa-inbox"></i>
              <p>No requests yet</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Get user ID from URL
    const urlParams = new URLSearchParams(window.location.search);
    const userId = urlParams.get('id');

    // Fetch user profile data
    async function loadUserProfile() {
      try {
        const response = await fetch(`api_get_user_details.php?id=${userId}`, {
          cache: 'no-store'
        });

        if (!response.ok) {
          throw new Error('Failed to fetch user data');
        }

        const user = await response.json();
        displayUserProfile(user);
      } catch (error) {
        console.error('Error loading user profile:', error);
        document.getElementById('loadingState').innerHTML = `
          <i class="fas fa-exclamation-triangle" style="font-size: 3rem; color: #ef4444; margin-bottom: 1rem;"></i>
          <p style="color: #ef4444;">Failed to load user profile</p>
          <p style="font-size: 0.875rem; color: #666;">Please try again later</p>
        `;
      }
    }

    function displayUserProfile(user) {
      // Hide loading, show profile
      document.getElementById('loadingState').style.display = 'none';
      document.getElementById('profileContainer').classList.add('show');

      // Profile Header
      document.getElementById('userName').textContent = user.fullName;
      document.getElementById('userEmail').textContent = user.email;
      
      // Avatar with initials
      const initials = (user.firstName.charAt(0) + user.lastName.charAt(0)).toUpperCase();
      document.getElementById('avatarCircle').textContent = initials;

      // Badges
      const badgesHtml = `
        <span class="badge ${user.isResident ? 'badge-resident' : 'badge-non-resident'}">
          <i class="fas fa-${user.isResident ? 'home' : 'map-marker-alt'}"></i>
          ${user.isResident ? 'Resident' : 'Non-Resident'}
        </span>
        <span class="badge badge-info">
          <i class="fas fa-id-card"></i>
          ${formatIdType(user.idType)}
        </span>
        <span class="badge badge-date">
          <i class="fas fa-calendar"></i>
          Joined ${formatDate(user.joinedDate)}
        </span>
      `;
      document.getElementById('userBadges').innerHTML = badgesHtml;

      // Stats
      document.getElementById('totalRequests').textContent = user.stats.totalRequests;
      document.getElementById('pendingRequests').textContent = user.stats.pendingRequests;
      document.getElementById('inProgressRequests').textContent = user.stats.inProgressRequests;
      document.getElementById('completedRequests').textContent = user.stats.completedRequests;

      // Personal Information
      document.getElementById('firstName').textContent = user.firstName || '-';
      document.getElementById('middleName').textContent = user.middleName || '-';
      document.getElementById('lastName').textContent = user.lastName || '-';
      document.getElementById('emailInfo').textContent = user.email || '-';
      document.getElementById('barangay').textContent = user.barangay || '-';
      document.getElementById('residentType').textContent = user.residentType || '-';
      document.getElementById('idType').textContent = formatIdType(user.idType) || '-';

      // ID Document Preview
      if (user.filePath && user.filePath !== 'uploads/default.jpg') {
        document.getElementById('idPreview').style.display = 'block';
        document.getElementById('idImage').src = user.filePath;
      }

      // Recent Requests
      if (user.recentRequests && user.recentRequests.length > 0) {
        const requestsHtml = user.recentRequests.map(req => `
          <div class="request-item">
            <div class="request-header">
              <span class="request-ticket">${req.ticketId}</span>
              <span class="request-status ${getStatusClass(req.status)}">${req.status}</span>
            </div>
            <div class="request-details">
              <span>${req.type}</span>
              <span>${formatDate(req.date)}</span>
            </div>
          </div>
        `).join('');
        document.getElementById('recentRequests').innerHTML = requestsHtml;
      }
    }

    function formatIdType(idType) {
      if (!idType) return '';
      return idType.split('-').map(word => 
        word.charAt(0).toUpperCase() + word.slice(1)
      ).join(' ');
    }

    function formatDate(dateString) {
      const date = new Date(dateString);
      return date.toLocaleDateString('en-US', { 
        month: 'short', 
        day: 'numeric', 
        year: 'numeric' 
      });
    }

    function getStatusClass(status) {
      const statusLower = status.toLowerCase();
      if (statusLower === 'completed') return 'status-completed';
      if (statusLower === 'in progress') return 'status-in-progress';
      return 'status-pending';
    }

    // Load profile on page load
    document.addEventListener('DOMContentLoaded', () => {
      if (userId) {
        loadUserProfile();
      } else {
        window.location.href = 'admindashboard.php';
      }
    });
  </script>
</body>
</html>