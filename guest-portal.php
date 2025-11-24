<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barangay 170 - eBCsH Guest Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(to bottom right, #d1fae5, #ecfdf5, #ccfbf1);
        }

        /* Header */
        header {
            background: white;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            background: transparent;
            border: none;
            cursor: pointer;
            transition: opacity 0.2s;
        }

        .logo-section:hover {
            opacity: 0.8;
        }

        .logo-img {
            height: 4rem;
            width: 4rem;
            border-radius: 50%;
        }

        .logo-text {
            text-align: left;
        }

        .logo-text h1 {
            font-size: 1.5rem;
            color: #14532d;
            margin: 0;
        }

        .logo-text p {
            color: #16a34a;
            margin: 0;
        }

        .header-buttons {
            display: flex;
            gap: 0.75rem;
        }

        .btn-signin {
            padding: 0.625rem 1.5rem;
            background: white;
            border: 2px solid #16a34a;
            color: #16a34a;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 1rem;
        }

        .btn-signin:hover {
            background: #f0fdf4;
        }

        .btn-signup {
            padding: 0.625rem 1.5rem;
            background: #16a34a;
            color: white;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 1rem;
        }

        .btn-signup:hover {
            background: #15803d;
        }

        /* Main Container */
        .main-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 3rem 1.5rem;
        }

        /* Hero Card */
        .hero-card {
            background: white;
            border-radius: 1.5rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .hero-header {
            background: linear-gradient(to right, #16a34a, #15803d);
            padding: 4rem 3rem;
            text-align: center;
        }

        .hero-header h2 {
            font-size: 2.5rem;
            color: white;
            margin-bottom: 1rem;
        }

        .hero-subtitle {
            font-size: 1.25rem;
            color: #dcfce7;
            margin-bottom: 1.5rem;
        }

        .hero-description {
            color: #d1fae5;
            max-width: 700px;
            margin: 0 auto;
            line-height: 1.6;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem;
            padding: 2rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .stat-item {
            text-align: center;
        }

        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }

        .stat-number {
            font-size: 1.5rem;
            color: #16a34a;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .stat-label {
            font-size: 0.875rem;
            color: #6b7280;
        }

        /* Services Section */
        .services-section {
            padding: 2rem;
        }

        .services-title {
            font-size: 1.5rem;
            color: #14532d;
            margin-bottom: 1.5rem;
            text-align: center;
            font-weight: 600;
        }

        .services-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        .service-card {
            background: #f0fdf4;
            padding: 1.25rem;
            border-radius: 0.5rem;
            border: 1px solid #dcfce7;
            transition: transform 0.2s;
        }

        .service-card:hover {
            transform: translateY(-2px);
        }

        .service-icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .service-card h4 {
            color: #15803d;
            margin-bottom: 0.25rem;
            font-size: 1rem;
            font-weight: 600;
        }

        .service-card p {
            font-size: 0.875rem;
            color: #166534;
            margin: 0;
        }

        /* Info Grid */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .info-card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            padding: 2rem;
        }

        .info-card-icon {
            width: 3.5rem;
            height: 3.5rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            background: #16a34a;
        }

        .info-card-icon.emergency {
            background: #ef4444;
        }

        .info-card-icon span {
            color: white;
            font-size: 1.5rem;
        }

        .info-card h3 {
            font-size: 1.25rem;
            color: #14532d;
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .info-card h3.emergency {
            color: #dc2626;
        }

        .contact-item {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            margin-bottom: 0.75rem;
        }

        .contact-icon {
            font-size: 1.125rem;
            margin-top: 0.25rem;
        }

        .contact-label {
            font-size: 0.875rem;
            color: #6b7280;
            margin: 0;
        }

        .contact-value {
            color: #14532d;
            margin: 0;
        }

        .emergency-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #f3f4f6;
            padding-bottom: 0.5rem;
            margin-bottom: 0.75rem;
        }

        .emergency-label {
            color: #6b7280;
        }

        .emergency-number {
            color: #14532d;
            font-weight: 500;
        }

        .hospital-section {
            margin-top: 1rem;
        }

        .hospital-label {
            font-size: 0.875rem;
            color: #6b7280;
            margin-bottom: 0.5rem;
        }

        .hospital-item {
            display: flex;
            justify-content: space-between;
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
        }

        /* Officials Card */
        .officials-card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            margin-top: 2rem;
        }

        .officials-title {
            font-size: 1.5rem;
            color: #14532d;
            margin-bottom: 1.5rem;
            text-align: center;
            font-weight: 600;
        }

        .officials-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
        }

        .official-item {
            text-align: center;
        }

        .official-avatar {
            background: #f0fdf4;
            width: 5rem;
            height: 5rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.75rem;
            border: 2px solid #16a34a;
        }

        .official-avatar span {
            font-size: 2.25rem;
        }

        .official-name {
            color: #14532d;
            margin-bottom: 0.25rem;
            font-weight: 600;
        }

        .official-position {
            font-size: 0.875rem;
            color: #16a34a;
        }

        /* CTA Section */
        .cta-section {
            background: linear-gradient(to right, #16a34a, #15803d);
            border-radius: 1rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            padding: 3rem;
            margin-top: 2rem;
            text-align: center;
        }

        .cta-title {
            font-size: 1.875rem;
            color: white;
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .cta-description {
            color: #dcfce7;
            margin-bottom: 2rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
            line-height: 1.6;
        }

        .cta-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }

        .btn-cta {
            padding: 0.875rem 2rem;
            background: white;
            color: #16a34a;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            font-size: 1rem;
            font-weight: 600;
        }

        .btn-cta:hover {
            background: #f0fdf4;
        }

        .signin-link {
            color: #d1fae5;
            font-size: 0.875rem;
            margin-top: 1rem;
        }

        .signin-link button {
            color: white;
            text-decoration: underline;
            cursor: pointer;
            background: transparent;
            border: none;
            font-size: 0.875rem;
        }

        /* Footer */
        footer {
            background: white;
            border-top: 1px solid #dcfce7;
            margin-top: 3rem;
        }

        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1.5rem;
            text-align: center;
            color: #15803d;
        }

        .footer-container p {
            margin-bottom: 0.5rem;
        }

        .footer-small {
            font-size: 0.875rem;
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .services-grid {
                grid-template-columns: 1fr;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .officials-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .header-buttons {
                flex-direction: column;
                gap: 0.5rem;
            }

            .hero-header {
                padding: 2rem 1.5rem;
            }

            .hero-header h2 {
                font-size: 1.75rem;
            }

            .hero-subtitle {
                font-size: 1rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .cta-section {
                padding: 2rem 1.5rem;
            }

            .cta-title {
                font-size: 1.5rem;
            }

            .main-container {
                padding: 1.5rem 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="header-container">
            <button class="logo-section" onclick="window.location.href='sign-in.php'">
                <img 
                    src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRTDCuh4kIpAtR-QmjA1kTjE_8-HSd8LSt3Gw&s" 
                    alt="Barangay Logo"
                    class="logo-img"
                />
                <div class="logo-text">
                    <h1>Barangay 170</h1>
                    <p>Deparo, Caloocan City</p>
                </div>
            </button>
            <div class="header-buttons">
                <button class="btn-signin" onclick="window.location.href='sign-in.php'">Sign In</button>
                <button class="btn-signup" onclick="window.location.href='sign-up.php'">Sign Up</button>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="main-container">
        <!-- Hero Section -->
        <div class="hero-card">
            <div class="hero-header">
                <h2>Welcome to eBCsH</h2>
                <p class="hero-subtitle">Electronic Barangay Certificate System for Health</p>
                <p class="hero-description">
                    Your one-stop digital portal for barangay-related health requests and community services. 
                    Access certificates, documents, and community health programs conveniently online.
                </p>
            </div>

            <!-- Quick Stats -->
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-icon">📋</div>
                    <div class="stat-number">10+</div>
                    <div class="stat-label">Document Types</div>
                </div>
                <div class="stat-item">
                    <div class="stat-icon">👥</div>
                    <div class="stat-number">15,000+</div>
                    <div class="stat-label">Residents Served</div>
                </div>
                <div class="stat-item">
                    <div class="stat-icon">⚡</div>
                    <div class="stat-number">Fast</div>
                    <div class="stat-label">Processing Time</div>
                </div>
                <div class="stat-item">
                    <div class="stat-icon">🏢</div>
                    <div class="stat-number">24/7</div>
                    <div class="stat-label">Online Access</div>
                </div>
            </div>

            <!-- Services Available -->
            <div class="services-section">
                <h3 class="services-title">Available Services</h3>
                <div class="services-grid">
                    <div class="service-card">
                        <div class="service-icon">📄</div>
                        <h4>Barangay ID</h4>
                        <p>Official identification for residents</p>
                    </div>
                    <div class="service-card">
                        <div class="service-icon">🏆</div>
                        <h4>Certificate of Indigency</h4>
                        <p>For scholarships and assistance</p>
                    </div>
                    <div class="service-card">
                        <div class="service-icon">👥</div>
                        <h4>Certificate of Residency</h4>
                        <p>Proof of residence document</p>
                    </div>
                    <div class="service-card">
                        <div class="service-icon">🏢</div>
                        <h4>Business Clearance</h4>
                        <p>For business operations</p>
                    </div>
                    <div class="service-card">
                        <div class="service-icon">📄</div>
                        <h4>Barangay Clearance</h4>
                        <p>General purpose clearance</p>
                    </div>
                    <div class="service-card">
                        <div class="service-icon">🏆</div>
                        <h4>Community Tax Certificate</h4>
                        <p>Cedula for residents</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Barangay Information -->
        <div class="info-grid">
            <!-- Contact Information -->
            <div class="info-card">
                <div class="info-card-icon">
                    <span>📞</span>
                </div>
                <h3>Contact Us</h3>
                <div class="contact-item">
                    <span class="contact-icon">📞</span>
                    <div>
                        <p class="contact-label">Hotline</p>
                        <p class="contact-value">(02) 8123-4567</p>
                    </div>
                </div>
                <div class="contact-item">
                    <span class="contact-icon">✉️</span>
                    <div>
                        <p class="contact-label">Email</p>
                        <p class="contact-value">K1contrerascris@gmail.com</p>
                    </div>
                </div>
                <div class="contact-item">
                    <span class="contact-icon">🕐</span>
                    <div>
                        <p class="contact-label">Office Hours</p>
                        <p class="contact-value">Mon-Fri, 8:00 AM - 5:00 PM</p>
                    </div>
                </div>
            </div>

            <!-- Location -->
            <div class="info-card">
                <div class="info-card-icon">
                    <span>📍</span>
                </div>
                <h3>Find Us</h3>
                <div class="contact-item">
                    <span class="contact-icon">📍</span>
                    <div>
                        <p class="contact-label">Barangay Hall</p>
                        <p class="contact-value">Deparo, Caloocan City</p>
                        <p class="contact-value">Metro Manila, Philippines</p>
                    </div>
                </div>
                <div class="contact-item">
                    <span class="contact-icon">🏢</span>
                    <div>
                        <p class="contact-label">Main Office</p>
                        <p class="contact-value">Ground Floor, Barangay Hall</p>
                    </div>
                </div>
            </div>

            <!-- Emergency Contacts -->
            <div class="info-card">
                <div class="info-card-icon emergency">
                    <span>📞</span>
                </div>
                <h3 class="emergency">Emergency Hotlines</h3>
                <div class="emergency-row">
                    <span class="emergency-label">Police</span>
                    <span class="emergency-number">(02) 8426-4663</span>
                </div>
                <div class="emergency-row">
                    <span class="emergency-label">Fire (BFP)</span>
                    <span class="emergency-number">(02) 8245 0849</span>
                </div>
                <div class="hospital-section">
                    <p class="hospital-label">Nearby Hospitals:</p>
                    <div class="hospital-item">
                        <span style="color: #14532d;">Camarin Doctors</span>
                        <span style="color: #6b7280;">2-7004-2881</span>
                    </div>
                    <div class="hospital-item">
                        <span style="color: #14532d;">Caloocan North</span>
                        <span style="color: #6b7280;">(02) 8288 7077</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Barangay Officials -->
        <div class="officials-card">
            <h3 class="officials-title">Barangay Officials</h3>
            <div class="officials-grid">
                <div class="official-item">
                    <div class="official-avatar">
                        <span>👥</span>
                    </div>
                    <h4 class="official-name">Hon. Maria Santos</h4>
                    <p class="official-position">Barangay Captain</p>
                </div>
                <div class="official-item">
                    <div class="official-avatar">
                        <span>📄</span>
                    </div>
                    <h4 class="official-name">Ms. Ana Cruz</h4>
                    <p class="official-position">Secretary</p>
                </div>
                <div class="official-item">
                    <div class="official-avatar">
                        <span>🏆</span>
                    </div>
                    <h4 class="official-name">Mr. Pedro Garcia</h4>
                    <p class="official-position">Treasurer</p>
                </div>
            </div>
        </div>

        <!-- CTA Section -->
        <div class="cta-section">
            <h3 class="cta-title">Ready to Get Started?</h3>
            <p class="cta-description">
                Create an account to submit requests, track your applications, and access all barangay health services online.
            </p>
            <div class="cta-buttons">
                <button class="btn-cta" onclick="window.location.href='sign-up.php'">Create Account</button>
            </div>
            <p class="signin-link">
                Already have an account? <button onclick="window.location.href='sign-in.php'">Sign In</button>
            </p>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="footer-container">
            <p>© 2025 Barangay 170, Deparo, Caloocan. All rights reserved.</p>
            <p class="footer-small">Electronic Barangay Certificate System for Health (eBCsH)</p>
        </div>
    </footer>
</body>
</html>