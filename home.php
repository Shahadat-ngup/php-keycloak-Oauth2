<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Debug: Check session contents
error_log('Session data in home.php: ' . print_r($_SESSION, true));

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    error_log('No user session found, redirecting to index.php');
    header('Location: index.php');
    exit;
}

$user = $_SESSION['user'];
error_log('User data loaded: ' . print_r($user, true));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SecureAuth</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            padding: 2rem 1rem;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 20px;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
            position: relative;
            overflow: hidden;
        }

        .header::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            transform: translate(30px, -30px);
        }

        .header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 150px;
            height: 150px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
            transform: translate(-50px, 50px);
        }

        .header-content {
            position: relative;
            z-index: 1;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .welcome-section h1 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            font-weight: 700;
        }

        .welcome-section .subtitle {
            font-size: 1.2rem;
            opacity: 0.9;
            font-weight: 300;
        }

        .user-actions {
            display: flex;
            gap: 1rem;
        }

        .btn {
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            cursor: pointer;
            font-size: 0.95rem;
        }

        .btn-primary {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .btn-primary:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }

        .btn-danger {
            background: #e74c3c;
            color: white;
        }

        .btn-danger:hover {
            background: #c0392b;
            transform: translateY(-2px);
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        .card-header {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .card-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            font-size: 1.5rem;
        }

        .card-icon.profile {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .card-icon.security {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }

        .card-icon.session {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
        }

        .card-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: #2c3e50;
        }

        .user-info {
            display: grid;
            gap: 1rem;
        }

        .info-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            background: #f8f9fc;
            border-radius: 10px;
            border-left: 4px solid #667eea;
        }

        .info-label {
            font-weight: 600;
            color: #2c3e50;
            min-width: 80px;
        }

        .info-value {
            color: #34495e;
            word-break: break-all;
        }

        .security-features {
            display: grid;
            gap: 1rem;
        }

        .security-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            background: #f8f9fc;
            border-radius: 10px;
        }

        .security-item i {
            color: #27ae60;
            margin-right: 1rem;
            font-size: 1.2rem;
        }

        .security-text {
            flex: 1;
        }

        .security-title {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.2rem;
        }

        .security-desc {
            color: #7f8c8d;
            font-size: 0.9rem;
        }

        .session-info {
            display: grid;
            gap: 1rem;
        }

        .session-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            background: #f8f9fc;
            border-radius: 10px;
        }

        .session-label {
            font-weight: 600;
            color: #2c3e50;
        }

        .session-value {
            color: #34495e;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
        }

        .status-badge {
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            background: #27ae60;
            color: white;
        }

        .footer-card {
            grid-column: 1 / -1;
            text-align: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .footer-card h3 {
            margin-bottom: 1rem;
            font-size: 1.8rem;
        }

        .footer-card p {
            opacity: 0.9;
            font-size: 1.1rem;
        }

        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                text-align: center;
            }

            .welcome-section h1 {
                font-size: 2rem;
            }

            .dashboard-grid {
                grid-template-columns: 1fr;
            }

            .user-actions {
                flex-direction: column;
                width: 100%;
            }

            .btn {
                justify-content: center;
            }
        }

        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .fade-in {
            animation: fadeIn 0.6s ease-in;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <div class="container fade-in">
        <div class="header">
            <div class="header-content">
                <div class="welcome-section">
                    <h1>Welcome, <?php echo htmlspecialchars($user['name'] ?? 'User'); ?>!</h1>
                    <div class="subtitle">You're successfully authenticated</div>
                </div>
                <div class="user-actions">
                    <a href="#" class="btn btn-primary">
                        <i class="fas fa-user-cog"></i>
                        Profile Settings
                    </a>
                    <a href="logout.php" class="btn btn-danger">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </a>
                </div>
            </div>
        </div>

        <div class="dashboard-grid">
            <div class="card">
                <div class="card-header">
                    <div class="card-icon profile">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="card-title">User Profile</div>
                </div>
                <div class="user-info">
                    <div class="info-item">
                        <div class="info-label">Name:</div>
                        <div class="info-value"><?php echo htmlspecialchars($user['name'] ?? 'Not provided'); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Email:</div>
                        <div class="info-value"><?php echo htmlspecialchars($user['email'] ?? 'Not provided'); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Username:</div>
                        <div class="info-value"><?php echo htmlspecialchars($user['preferred_username'] ?? 'Not provided'); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">User ID:</div>
                        <div class="info-value"><?php echo htmlspecialchars($user['sub'] ?? 'Not provided'); ?></div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-icon security">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div class="card-title">Security Features</div>
                </div>
                <div class="security-features">
                    <div class="security-item">
                        <i class="fas fa-check-circle"></i>
                        <div class="security-text">
                            <div class="security-title">OAuth2 + PKCE</div>
                            <div class="security-desc">Secure authentication flow</div>
                        </div>
                    </div>
                    <div class="security-item">
                        <i class="fas fa-check-circle"></i>
                        <div class="security-text">
                            <div class="security-title">CSRF Protection</div>
                            <div class="security-desc">State parameter validation</div>
                        </div>
                    </div>
                    <div class="security-item">
                        <i class="fas fa-check-circle"></i>
                        <div class="security-text">
                            <div class="security-title">Secure Sessions</div>
                            <div class="security-desc">HTTPOnly & Secure cookies</div>
                        </div>
                    </div>
                    <div class="security-item">
                        <i class="fas fa-check-circle"></i>
                        <div class="security-text">
                            <div class="security-title">SSO Ready</div>
                            <div class="security-desc">Single Sign-On enabled</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-icon session">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="card-title">Session Information</div>
                </div>
                <div class="session-info">
                    <div class="session-item">
                        <div class="session-label">Status:</div>
                        <div class="status-badge">Active</div>
                    </div>
                    <div class="session-item">
                        <div class="session-label">Login Time:</div>
                        <div class="session-value"><?php echo date('Y-m-d H:i:s'); ?></div>
                    </div>
                    <div class="session-item">
                        <div class="session-label">Session ID:</div>
                        <div class="session-value"><?php echo substr(session_id(), 0, 16) . '...'; ?></div>
                    </div>
                    <div class="session-item">
                        <div class="session-label">IP Address:</div>
                        <div class="session-value"><?php echo $_SERVER['REMOTE_ADDR'] ?? 'Unknown'; ?></div>
                    </div>
                </div>
            </div>

            <div class="card footer-card">
                <h3><i class="fas fa-rocket"></i> You're All Set!</h3>
                <p>Your application is secured with modern OAuth2 authentication and PKCE for enhanced security.</p>
            </div>
        </div>
    </div>
</body>
</html>