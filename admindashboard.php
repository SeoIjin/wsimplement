<?php
session_start();
require_once 'audit_trail_helper.php';

// Handle logout
if (isset($_POST['logout']) && $_POST['logout'] === 'true') {
    $admin_id = $_SESSION['user_id'] ?? 0;
    $admin_email = $_SESSION['user_email'] ?? 'Unknown';
    
    // Log logout
    logAdminLogout($admin_id, $admin_email);
    
    session_destroy();
    header('Location: sign-in.php');
    exit();
}

// require admin session
if (!isset($_SESSION['user_id']) || empty($_SESSION['is_admin'])) {
    header('Location: sign-in.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
  <title>Admin Dashboard - Barangay 170</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;300;400;500;600;700&display=swap" rel="stylesheet">
  <link href="css/admindashboard.css" rel="stylesheet">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
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
    }

    .header-actions .btn:hover {
      opacity: 0.9;
    }

    .header-actions .btn.logout {
      background: transparent;
      color: #333;
      border: 1px solid rgba(0,0,0,0.08);
    }

    .header-actions .btn.logout:hover {
      background: #f5f5f5;
    }

    /* Main Content */
    .main-content {
      padding: 2rem;
      max-width: 1600px;
      margin: 0 auto;
    }

    /* Analytics Cards */
    .analytics {
      display: flex;
      gap: 1rem;
      margin-bottom: 2rem;
      flex-wrap: wrap;
    }

    .card {
      background: white;
      border-radius: 0.5rem;
      padding: 1.5rem;
      flex: 1;
      min-width: 180px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .card h2 {
      font-size: 2rem;
      font-weight: 600;
      margin-bottom: 0.25rem;
    }

    .card small {
      color: #666;
      font-size: 0.875rem;
    }

    /* Analytics Graph Section */
    .analytics-graph-section {
      background: white;
      border-radius: 0.75rem;
      padding: 1.5rem;
      margin-bottom: 1.5rem;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    
    .graph-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1.25rem;
      flex-wrap: wrap;
      gap: 1rem;
    }
    
    .graph-header h2 {
      font-size: 1.25rem;
      font-weight: 600;
      color: #2c3e50;
      margin-bottom: 0.25rem;
    }
    
    .graph-header p {
      color: #7f8c8d;
      font-size: 0.875rem;
      font-weight: 300;
      margin: 0;
    }
    
    .timeframe-selector {
      display: flex;
      gap: 0.5rem;
      background: #f8f9fa;
      padding: 0.25rem;
      border-radius: 0.5rem;
    }
    
    .timeframe-btn {
      padding: 0.375rem 1rem;
      border: none;
      background: transparent;
      color: #7f8c8d;
      border-radius: 0.375rem;
      cursor: pointer;
      font-family: 'Poppins', sans-serif;
      font-size: 0.875rem;
      font-weight: 500;
      transition: all 0.3s ease;
    }
    
    .timeframe-btn:hover {
      background: #e9ecef;
    }
    
    .timeframe-btn.active {
      background: #2E5DFC;
      color: white;
    }
    
    .stats-summary {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
      gap: 1rem;
      margin-bottom: 1.5rem;
    }
    
    .stat-card-small {
      padding: 1.25rem;
      border-radius: 0.625rem;
      color: white;
    }
    
    .stat-card-small.blue {
      background: linear-gradient(135deg, #2E5DFC 0%, #4a76fc 100%);
    }
    
    .stat-card-small.orange {
      background: linear-gradient(135deg, #F66D31 0%, #ff8a5c 100%);
    }
    
    .stat-card-small.green {
      background: linear-gradient(135deg, #07A840 0%, #2bc965 100%);
    }
    
    .stat-card-small h3 {
      font-size: 1.875rem;
      font-weight: 700;
      margin-bottom: 0.25rem;
    }
    
    .stat-card-small p {
      font-size: 0.75rem;
      opacity: 0.95;
      font-weight: 400;
      margin: 0;
    }
    
    .chart-container {
      position: relative;
      height: 320px;
      margin-top: 1rem;
    }
    
    .loading-chart {
      display: none;
      justify-content: center;
      align-items: center;
      height: 320px;
      color: #7f8c8d;
    }
    
    .spinner {
      border: 3px solid #f3f3f3;
      border-top: 3px solid #2E5DFC;
      border-radius: 50%;
      width: 35px;
      height: 35px;
      animation: spin 1s linear infinite;
      margin-right: 12px;
    }
    
    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

    /* Content Section */
    .content-section {
      background: white;
      border-radius: 0.75rem;
      padding: 1.5rem;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
      margin-bottom: 1.5rem;
    }

    .content-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1.5rem;
      flex-wrap: wrap;
      gap: 1rem;
    }

    .content-header h1 {
      font-size: 1.5rem;
      font-weight: 300;
      margin-bottom: 0.25rem;
    }

    .content-header > div > p {
      color: #666;
      font-weight: 100;
      margin: 0;
      font-size: 0.875rem;
    }

    /* Tab Switcher */
    .tab-switcher {
      display: flex;
      gap: 0.5rem;
      background: #f8f9fa;
      padding: 0.25rem;
      border-radius: 0.5rem;
    }

    .tab-switcher button {
      padding: 0.5rem 1rem;
      border: none;
      background: transparent;
      color: #7f8c8d;
      border-radius: 0.375rem;
      cursor: pointer;
      font-family: 'Poppins', sans-serif;
      font-size: 0.875rem;
      font-weight: 500;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .tab-switcher button:hover {
      background: #e9ecef;
    }

    .tab-switcher button.active {
      background: #228650;
      color: white;
    }

    /* Search Bar */
    .search-bar {
      position: relative;
      margin-bottom: 1rem;
    }

    .search-bar i {
      position: absolute;
      left: 1rem;
      top: 50%;
      transform: translateY(-50%);
      color: #999;
    }

    .search-bar input {
      width: 100%;
      padding: 0.75rem 1rem 0.75rem 3rem;
      border: 1px solid #e0e0e0;
      border-radius: 0.5rem;
      outline: none;
      font-family: 'Poppins', sans-serif;
      font-size: 0.875rem;
      transition: border-color 0.2s;
    }

    .search-bar input:focus {
      border-color: #228650;
    }

    /* Filter Tabs */
    .filter-tabs {
      display: flex;
      gap: 0.5rem;
      margin-bottom: 1rem;
      flex-wrap: wrap;
    }

    .filter-tabs button {
      padding: 0.5rem 1rem;
      border: none;
      border-radius: 0.5rem;
      cursor: pointer;
      font-family: 'Poppins', sans-serif;
      font-size: 0.875rem;
      text-transform: capitalize;
      transition: all 0.2s;
      background: #f5f5f5;
      color: #333;
    }

    .filter-tabs button.active {
      background: #228650;
      color: white;
    }

    .filter-tabs button:hover:not(.active) {
      background: #e8e8e8;
    }

    /* Table */
    .table-container {
      overflow-x: auto;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 1rem;
    }

    thead tr {
      background: #f8f9fa;
    }

    th {
      text-align: left;
      padding: 0.75rem;
      border-bottom: 2px solid #dee2e6;
      font-weight: 500;
      font-size: 0.875rem;
    }

    tbody tr {
      border-bottom: 1px solid #dee2e6;
      transition: background 0.2s;
    }

    tbody tr:hover {
      background: #f8f9fa;
    }

    td {
      padding: 0.75rem;
      font-size: 0.875rem;
    }

    td.center {
      text-align: center;
      color: #666;
      padding: 1rem;
    }

    /* Badges */
    .priority-low {
      color: #006b3c;
      background: #d4f8e8;
      padding: 0.25rem 0.5rem;
      border-radius: 0.25rem;
      font-size: 0.875rem;
      display: inline-block;
    }

    .priority-medium {
      color: #9a7800;
      background: #fff3b0;
      padding: 0.25rem 0.5rem;
      border-radius: 0.25rem;
      font-size: 0.875rem;
      display: inline-block;
    }

    .priority-high {
      color: #a30000;
      background: #ffd1d1;
      padding: 0.25rem 0.5rem;
      border-radius: 0.25rem;
      font-size: 0.875rem;
      display: inline-block;
    }

    .status-under-review {
      color: #f39c12;
      background: #fff3e0;
      padding: 0.25rem 0.5rem;
      border-radius: 0.25rem;
      font-size: 0.875rem;
      display: inline-block;
    }

    .status-in-progress {
      color: #ff6b4a;
      background: #ffe8e4;
      padding: 0.25rem 0.5rem;
      border-radius: 0.25rem;
      font-size: 0.875rem;
      display: inline-block;
    }

    .status-ready {
      color: #505B6D;
      background: #e8ebed;
      padding: 0.25rem 0.5rem;
      border-radius: 0.25rem;
      font-size: 0.875rem;
      display: inline-block;
    }

    .status-completed {
      color: #07A840;
      background: #e6f8ec;
      padding: 0.25rem 0.5rem;
      border-radius: 0.25rem;
      font-size: 0.875rem;
      display: inline-block;
    }

    /* Actions */
    .actions a {
      margin-right: 0.75rem;
      color: #228650;
      text-decoration: none;
      transition: opacity 0.2s;
    }

    .actions a:last-child {
      color: #007bff;
    }

    .actions a:hover {
      opacity: 0.7;
    }

    /* Users Section */
    .users-header {
      background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
      color: white;
      padding: 1.5rem;
      border-radius: 0.5rem;
      margin-bottom: 1.5rem;
    }

    .users-header h2 {
      font-size: 2rem;
      margin-bottom: 0.5rem;
    }

    .users-header p {
      color: #dcfce7;
      margin: 0;
    }

    .users-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: 1rem;
    }

    .user-card {
      background: #f8f9fa;
      padding: 1.25rem;
      border-radius: 0.5rem;
      border: 1px solid #e0e0e0;
      transition: all 0.3s;
      cursor: pointer;
    }

    .user-card:hover {
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .user-card-header {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      margin-bottom: 1rem;
    }

    .user-avatar {
      background: #16a34a;
      color: white;
      width: 48px;
      height: 48px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.25rem;
    }

    .user-info h3 {
      margin: 0;
      color: #2c3e50;
      font-size: 1rem;
    }

    .user-info p {
      margin: 0;
      font-size: 0.75rem;
      color: #7f8c8d;
    }

    .user-details {
      display: flex;
      flex-direction: column;
      gap: 0.5rem;
      margin-bottom: 1rem;
    }

    .user-detail-row {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      font-size: 0.875rem;
    }

    .user-detail-label {
      color: #7f8c8d;
    }

    .user-detail-value {
      color: #2c3e50;
    }

    .badge-resident {
      background: #d4f8e8;
      color: #006b3c;
      padding: 0.125rem 0.5rem;
      border-radius: 0.25rem;
      font-size: 0.75rem;
    }

    .badge-non-resident {
      background: #fff3e0;
      color: #9a4500;
      padding: 0.125rem 0.5rem;
      border-radius: 0.25rem;
      font-size: 0.75rem;
    }

    .user-card button {
      width: 100%;
      background: #16a34a;
      color: white;
      padding: 0.5rem;
      border: none;
      border-radius: 0.375rem;
      cursor: pointer;
      font-family: 'Poppins', sans-serif;
      font-size: 0.875rem;
      transition: background 0.3s;
    }

    .user-card button:hover {
      background: #15803d;
    }

    /* Notifications Section */
    .notifications-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1.25rem;
    }

    .notifications-title {
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }

    .notifications-title h2 {
      font-size: 1.25rem;
      color: #2c3e50;
      margin: 0;
    }

    .btn-collapse {
      background: #f8f9fa;
      color: #7f8c8d;
      padding: 0.25rem 0.75rem;
      border-radius: 0.375rem;
      cursor: pointer;
      border: none;
      font-size: 0.75rem;
      font-weight: 500;
      transition: background 0.3s;
    }

    .btn-collapse:hover {
      background: #e9ecef;
    }

    .btn-add {
      background: #2E5DFC;
      color: white;
      padding: 0.375rem 1rem;
      border-radius: 0.375rem;
      cursor: pointer;
      border: none;
      font-size: 0.875rem;
      display: flex;
      align-items: center;
      gap: 0.25rem;
      transition: opacity 0.3s;
    }

    .btn-add:hover {
      opacity: 0.9;
    }

    .notification-form {
      background: #f8f9fa;
      padding: 1rem;
      border-radius: 0.5rem;
      margin-bottom: 1rem;
    }

    .notification-type-btns {
      display: flex;
      gap: 0.5rem;
      margin-bottom: 0.5rem;
    }

    .notification-type-btns button {
      padding: 0.375rem 1rem;
      border-radius: 0.375rem;
      border: none;
      cursor: pointer;
      font-family: 'Poppins', sans-serif;
      font-size: 0.875rem;
      font-weight: 500;
      transition: all 0.3s;
    }

    .notification-type-btns button.news {
      background: transparent;
      color: #7f8c8d;
    }

    .notification-type-btns button.news.active {
      background: #07A840;
      color: white;
    }

    .notification-type-btns button.event {
      background: transparent;
      color: #7f8c8d;
    }

    .notification-type-btns button.event.active {
      background: #F66D31;
      color: white;
    }

    .notification-form input,
    .notification-form textarea {
      width: 100%;
      padding: 0.75rem 1rem;
      border: 1px solid #e0e0e0;
      border-radius: 0.5rem;
      outline: none;
      font-family: 'Poppins', sans-serif;
      font-size: 0.875rem;
      margin-bottom: 0.5rem;
    }

    .notification-form input:focus,
    .notification-form textarea:focus {
      border-color: #228650;
    }

    .notification-list {
      display: flex;
      flex-direction: column;
      gap: 1rem;
    }

    .notification-item {
      padding: 1rem;
      border-radius: 0.5rem;
      transition: all 0.3s;
    }

    .notification-item.news {
      background: #d4f8e8;
    }

    .notification-item.event {
      background: #fff3e0;
    }

    .notification-item-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 0.5rem;
    }

    .notification-item-title {
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .notification-badge {
      padding: 0.25rem 0.5rem;
      border-radius: 0.25rem;
      font-size: 0.75rem;
      font-weight: 500;
    }

    .notification-badge.news {
      background: #07A840;
      color: white;
    }

    .notification-badge.event {
      background: #F66D31;
      color: white;
    }

    .notification-item h3 {
      font-size: 0.875rem;
      font-weight: 600;
      margin: 0;
    }

    .btn-delete {
      background: transparent;
      border: none;
      color: #9a4500;
      cursor: pointer;
      transition: opacity 0.3s;
      font-size: 1rem;
    }

    .btn-delete:hover {
      opacity: 0.7;
    }

    .notification-date {
      font-size: 0.75rem;
      color: #666;
      margin-bottom: 0.5rem;
    }

    .notification-description {
      font-size: 0.875rem;
      margin: 0;
    }

    .empty-state {
      text-align: center;
      padding: 2rem;
      color: #7f8c8d;
    }

    .hidden {
      display: none;
    }

    @media (max-width: 768px) {
      .main-content {
        padding: 1rem;
      }

      .analytics {
        flex-direction: column;
      }

      .graph-header,
      .content-header {
        flex-direction: column;
        align-items: flex-start;
      }

      .stats-summary {
        grid-template-columns: 1fr;
      }

      .users-grid {
        grid-template-columns: 1fr;
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
        <div>Admin Dashboard</div>
      </div>
    </div>
    <div class="header-actions">
      <button class="btn" id="auditBtn">Audit Trail</button>
      <button class="btn" id="requestBtn">Request</button>
      <button class="btn logout" id="logoutBtn">Logout</button>
    </div>
  </div>

  <div class="main-content">
    <!-- Analytics Cards -->
    <div class="analytics">
      <div class="card">
        <h2 id="totalCount" style="color: #2E5DFC;">0</h2>
        <small>Total</small>
      </div>
      <div class="card">
        <h2 id="reviewCount" style="color: #F66D31;">0</h2>
        <small>Pending</small>
      </div>
      <div class="card">
        <h2 id="progressCount" style="color: #E27508;">0</h2>
        <small>In Progress</small>
      </div>
      <div class="card">
        <h2 id="readyCount" style="color: #505B6D;">0</h2>
        <small>Ready</small>
      </div>
      <div class="card">
        <h2 id="completedCount" style="color: #07A840;">0</h2>
        <small>Completed</small>
      </div>
    </div>

    <!-- Analytics Graph Section -->
    <div class="analytics-graph-section">
      <div class="graph-header">
        <div>
          <h2>📊 Report Graph</h2>
          <p>Track reports over time</p>
        </div>
        <div class="timeframe-selector">
          <button class="timeframe-btn active" data-timeframe="day">Today</button>
          <button class="timeframe-btn" data-timeframe="week">This Week</button>
          <button class="timeframe-btn" data-timeframe="month">This Month</button>
        </div>
      </div>
      
      <div class="stats-summary">
        <div class="stat-card-small blue">
          <h3 id="graphTotalRequests">0</h3>
          <p>Total Requests</p>
        </div>
        <div class="stat-card-small orange">
          <h3 id="graphAvgPerPeriod">0</h3>
          <p id="graphAvgLabel">Avg per Hour</p>
        </div>
        <div class="stat-card-small green">
          <h3 id="graphPeakValue">0</h3>
          <p id="graphPeakLabel">Peak Hour</p>
        </div>
      </div>
      
      <div class="chart-container">
        <canvas id="analyticsChart"></canvas>
      </div>
      
      <div id="loadingChartIndicator" class="loading-chart">
        <div class="spinner"></div>
        <span>Loading data...</span>
      </div>
    </div>

    <!-- Main Content Section -->
    <div class="content-section">
      <div class="content-header">
        <div>
          <h1 id="sectionTitle">Request Management</h1>
          <p id="sectionDescription">Manage and track all requests from citizens</p>
        </div>
        <div class="tab-switcher">
          <button class="active" id="requestsTab">
            <i class="fas fa-file-alt"></i>
            Requests
          </button>
          <button id="usersTab">
            <i class="fas fa-users"></i>
            Users
          </button>
        </div>
      </div>

      <!-- Requests Section -->
      <div id="requestsSection">
        <div class="search-bar">
          <i class="fa fa-search"></i>
          <input type="text" id="searchInput" placeholder="Search by ID, name, or request type...">
        </div>

        <div class="filter-tabs">
          <button class="active" data-status="all">All</button>
          <button data-status="review">Pending</button>
          <button data-status="progress">In Progress</button>
          <button data-status="ready">Ready</button>
          <button data-status="done">Completed Report</button>
        </div>

        <div class="table-container">
          <table>
            <thead>
              <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Type</th>
                <th>Priority</th>
                <th>Status</th>
                <th>Date</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="requestTableBody"></tbody>
          </table>
        </div>
      </div>

      <!-- Users Section -->
      <div id="usersSection" class="hidden">
        <div class="users-header">
          <h2 id="userCount">0</h2>
          <p>Total Registered Users</p>
        </div>
        <div class="users-grid" id="usersGrid"></div>
      </div>
    </div>

    <!-- Notifications Section -->
    <div class="content-section">
      <div class="notifications-header">
        <div class="notifications-title">
          <h2>🔔 Notifications</h2>
          <button class="btn-collapse hidden" id="collapseBtn">Show All (0)</button>
        </div>
        <button class="btn-add" id="addNotificationBtn">
          <i class="fas fa-plus"></i>
        </button>
      </div>

      <!-- Notification Form -->
      <div id="notificationForm" class="notification-form hidden">
        <div class="notification-type-btns">
          <button class="news active" data-type="NEWS">News</button>
          <button class="event" data-type="EVENT">Event</button>
        </div>
        <input type="text" id="notifTitle" placeholder="Title">
        <input type="text" id="notifDate" placeholder="Date">
        <textarea id="notifDescription" rows="3" placeholder="Description"></textarea>
        <button class="btn-add" id="submitNotificationBtn">Add Notification</button>
      </div>

      <!-- Notifications List -->
      <div id="notificationsList" class="notification-list"></div>
      <div id="emptyNotifications" class="empty-state">
        <p>No notifications yet. Click + to add one.</p>
      </div>
    </div>
  </div>
  <script>
    // Global variables
  let currentFilter = "all";
  let currentTimeframe = 'day';
  let currentTab = 'requests';
  let analyticsChart = null;
  let notificationType = 'NEWS';
  let notifications = [];
  let notificationsExpanded = true;

  // Fetch requests from server API
  async function fetchRequests() {
    try {
      const res = await fetch('api_get_requests.php', {cache: 'no-store'});
      if (!res.ok) {
        console.error('Failed to fetch requests', res.status);
        return [];
      }
      const data = await res.json();
      return Array.isArray(data) ? data : [];
    } catch (err) {
      console.error('Error fetching requests', err);
      return [];
    }
  }

  // Fetch users (you'll need to create this API endpoint)
  async function fetchUsers() {
    try {
      const res = await fetch('api_get_users.php', {cache: 'no-store'});
      if (!res.ok) {
        console.error('Failed to fetch users', res.status);
        return [];
      }
      const data = await res.json();
      return Array.isArray(data) ? data : [];
    } catch (err) {
      console.error('Error fetching users', err);
      return [];
    }
  }

  // Load and render requests
  async function loadRequests() {
    const tableBody = document.getElementById("requestTableBody");
    const requests = await fetchRequests();
    const searchInput = document.getElementById("searchInput")?.value.toLowerCase() || "";

    // Apply search filter
    let filteredRequests = requests.filter(r =>
      ('' + (r.id || '')).toLowerCase().includes(searchInput) ||
      (r.name || '').toLowerCase().includes(searchInput) ||
      (r.type || '').toLowerCase().includes(searchInput)
    );

    // Apply tab (status) filter
    if (currentFilter !== "all") {
      filteredRequests = filteredRequests.filter(r => {
        const status = (r.status || '').toLowerCase();
        if (currentFilter === "review") return status === "under review" || status === "review" || status === "pending";
        if (currentFilter === "progress") return status === "in progress" || status === "progress";
        if (currentFilter === "ready") return status === "ready";
        if (currentFilter === "done") return status === "completed" || status === "done";
        return true;
      });
    }

    // Populate table
    tableBody.innerHTML = "";
    if (filteredRequests.length === 0) {
      tableBody.innerHTML = `<tr><td colspan="7" class="center">No matching requests</td></tr>`;
    } else {
      filteredRequests.forEach(r => {
        const priority = (r.priority || 'Medium').toLowerCase();
        const priorityClass =
          priority === "low" ? "priority-low" : priority === "medium" ? "priority-medium" : "priority-high";

        const st = (r.status || '').toLowerCase();
        const statusClass = st === "under review" ? "status-under-review" : st === "in progress" ? "status-in-progress" : st === "ready" ? "status-ready" : "status-completed";

        tableBody.innerHTML += `
          <tr>
            <td>${r.ticket_id || r.id}</td>
            <td>${r.name}</td>
            <td>${r.type}</td>
            <td><span class="${priorityClass}">${r.priority || 'Medium'}</span></td>
            <td><span class="${statusClass}">${r.status || 'New'}</span></td>
            <td>${r.submitted}</td>
            <td class="actions">
              <a href="ReqDet&Upd.php?ticket_id=${encodeURIComponent(r.ticket_id || r.id)}"><i class="fa fa-eye"></i></a>
              <a href="ReqDet&Upd.php?ticket_id=${encodeURIComponent(r.ticket_id || r.id)}"><i class="fa fa-edit"></i></a>
            </td>
          </tr>`;
      });
    }
    
    updateDashboard(requests);
  }

  // Load and render users
  async function loadUsers() {
    const usersGrid = document.getElementById("usersGrid");
    const users = await fetchUsers();
    
    document.getElementById("userCount").textContent = users.length;
    
    if (users.length === 0) {
      usersGrid.innerHTML = `
        <div class="empty-state" style="grid-column: 1/-1;">
          <i class="fas fa-users" style="font-size: 4rem; opacity: 0.3; margin-bottom: 1rem;"></i>
          <p style="font-size: 1.125rem;">No users registered yet</p>
          <p style="font-size: 0.875rem;">Users will appear here when they sign up</p>
        </div>`;
    } else {
      usersGrid.innerHTML = users.map(user => `
        <div class="user-card">
          <div class="user-card-header">
            <div class="user-avatar">
              <i class="fas fa-user"></i>
            </div>
            <div class="user-info">
              <h3>${user.name}</h3>
              <p>${user.email}</p>
            </div>
          </div>
          <div class="user-details">
            ${user.isResident !== undefined ? `
              <div class="user-detail-row">
                <span class="user-detail-label">Status:</span>
                <span class="${user.isResident ? 'badge-resident' : 'badge-non-resident'}">
                  ${user.isResident ? 'Resident' : 'Non-Resident'}
                </span>
              </div>
            ` : ''}
            ${user.idType ? `
              <div class="user-detail-row">
                <span class="user-detail-label">ID Type:</span>
                <span class="user-detail-value" style="text-transform: capitalize;">
                  ${user.idType.replace(/-/g, ' ')}
                </span>
              </div>
            ` : ''}
            ${user.joinedDate ? `
              <div class="user-detail-row">
                <span class="user-detail-label">Joined:</span>
                <span class="user-detail-value">
                  ${new Date(user.joinedDate).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}
                </span>
              </div>
            ` : ''}
          </div>
          <button onclick="viewUserProfile(${user.id})">View Full Profile</button>
        </div>
      `).join('');
    }
  }

  // Add this new function to handle profile view
  function viewUserProfile(userId) {
    window.location.href = `user-profile.php?id=${userId}`;
  }

  // Update dashboard analytics
  function updateDashboard(reqs) {
    const counts = {
      total: reqs.length,
      review: reqs.filter(r => r.status.toLowerCase() === "pending" || r.status.toLowerCase() === "pending").length,
      progress: reqs.filter(r => r.status.toLowerCase() === "in progress").length,
      ready: reqs.filter(r => r.status.toLowerCase() === "ready").length,
      completed: reqs.filter(r => r.status.toLowerCase() === "completed").length,
    };
    document.getElementById("totalCount").textContent = counts.total;
    document.getElementById("reviewCount").textContent = counts.review;
    document.getElementById("progressCount").textContent = counts.progress;
    document.getElementById("readyCount").textContent = counts.ready;
    document.getElementById("completedCount").textContent = counts.completed;
  }

  // Initialize analytics chart
  function initAnalyticsChart() {
    const ctx = document.getElementById('analyticsChart').getContext('2d');
    
    analyticsChart = new Chart(ctx, {
      type: 'line',
      data: {
        labels: [],
        datasets: [{
          label: 'Requests',
          data: [],
          borderColor: '#2E5DFC',
          backgroundColor: 'rgba(46, 93, 252, 0.1)',
          borderWidth: 3,
          fill: true,
          tension: 0.4,
          pointRadius: 4,
          pointHoverRadius: 6,
          pointBackgroundColor: '#2E5DFC',
          pointBorderColor: '#fff',
          pointBorderWidth: 2
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false
          },
          tooltip: {
            backgroundColor: 'rgba(0, 0, 0, 0.8)',
            padding: 12,
            titleFont: {
              size: 14,
              family: 'Poppins'
            },
            bodyFont: {
              size: 13,
              family: 'Poppins'
            },
            displayColors: false
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              stepSize: 1,
              font: {
                family: 'Poppins',
                size: 11
              }
            },
            grid: {
              color: 'rgba(0, 0, 0, 0.05)'
            }
          },
          x: {
            ticks: {
              font: {
                family: 'Poppins',
                size: 10
              },
              maxRotation: 45,
              minRotation: 45
            },
            grid: {
              display: false
            }
          }
        }
      }
    });
  }

  // Fetch analytics data
  async function fetchAnalytics(timeframe) {
    try {
      const response = await fetch(`api_get_analytics.php?timeframe=${timeframe}`, {
        cache: 'no-store'
      });
      
      if (!response.ok) {
        throw new Error('Failed to fetch analytics');
      }
      
      const data = await response.json();
      return data;
    } catch (error) {
      console.error('Error fetching analytics:', error);
      return [];
    }
  }

  // Update chart with new data
  async function updateAnalyticsChart(timeframe) {
    document.getElementById('loadingChartIndicator').style.display = 'flex';
    document.querySelector('.chart-container canvas').style.opacity = '0.3';
    
    const data = await fetchAnalytics(timeframe);
    
    if (analyticsChart && data.length > 0) {
      analyticsChart.data.labels = data.map(d => d.label);
      analyticsChart.data.datasets[0].data = data.map(d => d.value);
      analyticsChart.update('none');
      
      updateAnalyticsStats(data, timeframe);
    }
    
    document.getElementById('loadingChartIndicator').style.display = 'none';
    document.querySelector('.chart-container canvas').style.opacity = '1';
  }

  // Update summary statistics
  function updateAnalyticsStats(data, timeframe) {
    const total = data.reduce((sum, item) => sum + item.value, 0);
    const avg = data.length > 0 ? (total / data.length).toFixed(1) : 0;
    const peak = Math.max(...data.map(d => d.value));
    const peakIndex = data.findIndex(d => d.value === peak);
    const peakLabel = peakIndex >= 0 ? data[peakIndex].label : '-';
    
    document.getElementById('graphTotalRequests').textContent = total;
    document.getElementById('graphAvgPerPeriod').textContent = avg;
    document.getElementById('graphPeakValue').textContent = peak;
    
    if (timeframe === 'day') {
      document.getElementById('graphAvgLabel').textContent = 'Avg per Hour';
      document.getElementById('graphPeakLabel').textContent = `Peak: ${peakLabel}`;
    } else if (timeframe === 'week') {
      document.getElementById('graphAvgLabel').textContent = 'Avg per Day';
      document.getElementById('graphPeakLabel').textContent = `Peak: ${peakLabel}`;
    } else {
      document.getElementById('graphAvgLabel').textContent = 'Avg per Day';
      document.getElementById('graphPeakLabel').textContent = `Peak Day`;
    }
  }

  // Fetch notifications from server
  async function fetchNotifications() {
    try {
      const res = await fetch('api_get_notifications.php', {cache: 'no-store'});
      if (!res.ok) {
        console.error('Failed to fetch notifications', res.status);
        return [];
      }
      const data = await res.json();
      return Array.isArray(data) ? data : [];
    } catch (err) {
      console.error('Error fetching notifications', err);
      return [];
    }
  }

  // Load and render notifications
  async function loadNotifications() {
    notifications = await fetchNotifications();
    renderNotifications();
  }

  // Render notifications
  function renderNotifications() {
    const notificationsList = document.getElementById('notificationsList');
    const emptyNotifications = document.getElementById('emptyNotifications');
    const collapseBtn = document.getElementById('collapseBtn');
    
    if (notifications.length === 0) {
      notificationsList.innerHTML = '';
      emptyNotifications.classList.remove('hidden');
      collapseBtn.classList.add('hidden');
      return;
    }
    
    emptyNotifications.classList.add('hidden');
    
    if (notifications.length > 3) {
      collapseBtn.classList.remove('hidden');
      collapseBtn.textContent = notificationsExpanded ? 'Collapse' : `Show All (${notifications.length})`;
    } else {
      collapseBtn.classList.add('hidden');
    }
    
    const displayNotifications = notificationsExpanded ? notifications : notifications.slice(0, 3);
    
    notificationsList.innerHTML = displayNotifications.map(notif => `
      <div class="notification-item ${notif.type.toLowerCase()}">
        <div class="notification-item-header">
          <div class="notification-item-title">
            <span class="notification-badge ${notif.type.toLowerCase()}">${notif.type}</span>
            <h3>${notif.title}</h3>
          </div>
          <button class="btn-delete" onclick="deleteNotification(${notif.id})">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <p class="notification-date">${notif.date}</p>
        <p class="notification-description">${notif.description}</p>
      </div>
    `).join('');
  }

  // Add notification
  async function addNotification() {
    const title = document.getElementById('notifTitle').value;
    const date = document.getElementById('notifDate').value;
    const description = document.getElementById('notifDescription').value;
    
    if (!title || !date || !description) {
      alert('Please fill in all fields');
      return;
    }
    
    try {
      const response = await fetch('api_add_notification.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          type: notificationType,
          title: title,
          date: date,
          description: description
        })
      });
      
      const result = await response.json();
      
      if (response.ok && result.success) {
        // Clear form
        document.getElementById('notifTitle').value = '';
        document.getElementById('notifDate').value = '';
        document.getElementById('notifDescription').value = '';
        document.getElementById('notificationForm').classList.add('hidden');
        
        // Reload notifications
        await loadNotifications();
        
        alert('Notification added successfully!');
      } else {
        alert('Error: ' + (result.error || 'Failed to add notification'));
      }
    } catch (err) {
      console.error('Error adding notification:', err);
      alert('Failed to add notification. Please try again.');
    }
  }

  // Delete notification
  async function deleteNotification(id) {
    if (!confirm('Are you sure you want to delete this notification?')) {
      return;
    }
    
    try {
      const response = await fetch(`api_delete_notification.php?id=${id}`, {
        method: 'DELETE'
      });
      
      const result = await response.json();
      
      if (response.ok && result.success) {
        // Reload notifications
        await loadNotifications();
        alert('Notification deleted successfully!');
      } else {
        alert('Error: ' + (result.error || 'Failed to delete notification'));
      }
    } catch (err) {
      console.error('Error deleting notification:', err);
      alert('Failed to delete notification. Please try again.');
    }
  }

  // Event Listeners
  document.addEventListener('DOMContentLoaded', () => {
    // Initialize
    loadRequests();
    loadNotifications();
    initAnalyticsChart();
    updateAnalyticsChart(currentTimeframe);
    
    // Search input
    const searchInput = document.getElementById('searchInput');
    if (searchInput) searchInput.addEventListener('input', loadRequests);
    
    // Filter tabs
    document.querySelectorAll('.filter-tabs button').forEach(btn => {
      btn.addEventListener('click', () => {
        document.querySelectorAll('.filter-tabs button').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        currentFilter = btn.dataset.status;
        loadRequests();
      });
    });
    
    // Timeframe buttons
    document.querySelectorAll('.timeframe-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        document.querySelectorAll('.timeframe-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        currentTimeframe = btn.dataset.timeframe;
        updateAnalyticsChart(currentTimeframe);
      });
    });
    
    // Tab switcher
    document.getElementById('requestsTab').addEventListener('click', () => {
      document.getElementById('requestsTab').classList.add('active');
      document.getElementById('usersTab').classList.remove('active');
      document.getElementById('requestsSection').classList.remove('hidden');
      document.getElementById('usersSection').classList.add('hidden');
      document.getElementById('sectionTitle').textContent = 'Request Management';
      document.getElementById('sectionDescription').textContent = 'Manage and track all requests from citizens';
      currentTab = 'requests';
    });
    
    document.getElementById('usersTab').addEventListener('click', () => {
      document.getElementById('usersTab').classList.add('active');
      document.getElementById('requestsTab').classList.remove('active');
      document.getElementById('usersSection').classList.remove('hidden');
      document.getElementById('requestsSection').classList.add('hidden');
      document.getElementById('sectionTitle').textContent = 'User Management';
      document.getElementById('sectionDescription').textContent = 'View and manage registered users';
      currentTab = 'users';
      loadUsers();
    });
    
    // Notification form toggle
    document.getElementById('addNotificationBtn').addEventListener('click', () => {
      const form = document.getElementById('notificationForm');
      form.classList.toggle('hidden');
    });
    
    // Notification type buttons
    document.querySelectorAll('.notification-type-btns button').forEach(btn => {
      btn.addEventListener('click', () => {
        document.querySelectorAll('.notification-type-btns button').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        notificationType = btn.dataset.type;
      });
    });
    
    // Replace the addNotification function in admindashboard.php with this:

async function addNotification() {
  const title = document.getElementById('notifTitle').value;
  const date = document.getElementById('notifDate').value;
  const description = document.getElementById('notifDescription').value;
  
  if (!title || !date || !description) {
    alert('Please fill in all fields');
    return;
  }
  
  try {
    const response = await fetch('api_add_notification.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        type: notificationType,
        title: title,
        date: date,
        description: description
      })
    });
    
    // Get the response text first to see what's returned
    const responseText = await response.text();
    console.log('Server response:', responseText); // Debug log
    
    let result;
    try {
      result = JSON.parse(responseText);
    } catch (e) {
      console.error('Failed to parse JSON:', responseText);
      alert('Server error: Invalid response format. Check console for details.');
      return;
    }
    
    if (response.ok && result.success) {
      // Clear form
      document.getElementById('notifTitle').value = '';
      document.getElementById('notifDate').value = '';
      document.getElementById('notifDescription').value = '';
      document.getElementById('notificationForm').classList.add('hidden');
      
      // Reload notifications
      await loadNotifications();
      
      alert('Notification added successfully!');
    } else {
      // Show the actual error from the server
      console.error('Error details:', result);
      alert('Error: ' + (result.error || result.message || 'Failed to add notification. Check console for details.'));
    }
  } catch (err) {
    console.error('Error adding notification:', err);
    alert('Network error: ' + err.message + '. Check if api_add_notification.php exists and the database is running.');
  }
}

    // Submit notification
    document.getElementById('submitNotificationBtn').addEventListener('click', addNotification);
    
    // Collapse notifications
    document.getElementById('collapseBtn').addEventListener('click', () => {
      notificationsExpanded = !notificationsExpanded;
      renderNotifications();
    });
    
    // Header buttons
    document.getElementById('auditBtn').addEventListener('click', () => {
      window.location.href = 'audit_trail.php';
    });

    document.getElementById('requestBtn').addEventListener('click', () => {
      window.location.href = 'ReqDet&Upd.php';
    });
    
    document.getElementById('logoutBtn').addEventListener('click', () => {
      window.location.href = 'sign-in.php';
    });
    
    // Auto-refresh
    setInterval(loadRequests, 30000);
    setInterval(() => updateAnalyticsChart(currentTimeframe), 30000);
  });
  </script>
</body>
</html>