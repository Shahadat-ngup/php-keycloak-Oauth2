<?php
session_start();

// If user is already logged in, redirect to home
if (isset($_SESSION['user'])) {
    header('Location: home.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SecureAuth - Login with Keycloak</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 3rem 2.5rem;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 450px;
            width: 100%;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .logo {
            margin-bottom: 2rem;
        }

        .logo i {
            font-size: 4rem;
            color: #667eea;
            margin-bottom: 1rem;
        }

        .app-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .app-subtitle {
            color: #7f8c8d;
            font-size: 1.1rem;
            margin-bottom: 2rem;
        }

        .welcome-text {
            color: #34495e;
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .instruction-text {
            color: #7f8c8d;
            margin-bottom: 2.5rem;
            font-size: 1rem;
        }

        .login-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 1rem 2.5rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
            border: none;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 35px rgba(102, 126, 234, 0.4);
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
        }

        .login-btn:active {
            transform: translateY(0);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.3);
        }

        .login-btn i {
            margin-right: 0.8rem;
            font-size: 1.2rem;
        }

        .features {
            margin-top: 2.5rem;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 1rem;
        }

        .feature {
            padding: 1rem;
            background: rgba(102, 126, 234, 0.1);
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .feature:hover {
            transform: translateY(-2px);
            background: rgba(102, 126, 234, 0.15);
        }

        .feature i {
            color: #667eea;
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .feature-title {
            font-size: 0.9rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.3rem;
        }

        .feature-desc {
            font-size: 0.8rem;
            color: #7f8c8d;
        }

        .footer {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(127, 140, 141, 0.2);
            color: #95a5a6;
            font-size: 0.9rem;
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 2rem 1.5rem;
            }
            
            .app-title {
                font-size: 2rem;
            }
            
            .features {
                grid-template-columns: 1fr;
            }
        }

        .pulse {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
            100% {
                transform: scale(1);
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <i class="fas fa-shield-alt pulse"></i>
            <div class="app-title">SecureAuth</div>
            <div class="app-subtitle">OAuth2 + PKCE Authentication</div>
        </div>

        <div class="welcome-text">Welcome to Our Application</div>
        <div class="instruction-text">Secure authentication powered by Keycloak</div>

        <a href="login.php" class="login-btn">
            <i class="fas fa-sign-in-alt"></i>
            Login with Keycloak
        </a>

        <div class="features">
            <div class="feature">
                <i class="fas fa-lock"></i>
                <div class="feature-title">Secure</div>
                <div class="feature-desc">OAuth2 + PKCE</div>
            </div>
            <div class="feature">
                <i class="fas fa-users"></i>
                <div class="feature-title">SSO Ready</div>
                <div class="feature-desc">Single Sign-On</div>
            </div>
            <div class="feature">
                <i class="fas fa-mobile-alt"></i>
                <div class="feature-title">Responsive</div>
                <div class="feature-desc">All Devices</div>
            </div>
        </div>

        <div class="footer">
            Powered by Keycloak • OAuth2 • PKCE
        </div>
    </div>
</body>
</html>