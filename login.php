<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/KeycloakProvider.php';
require_once __DIR__ . '/keycloak-config.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Debug: Log all request parameters
error_log('Login.php called with: ' . print_r($_GET, true));

// Secure session configuration
session_set_cookie_params([
    'lifetime' => 86400,
    'path' => '/',
    'secure' => ($_ENV['APP_ENV'] ?? 'development') === 'production',
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();

// Enforce HTTPS in production
if (($_ENV['APP_ENV'] ?? 'development') === 'production' && ($_SERVER['HTTPS'] ?? 'off') !== 'on') {
    header("Location: https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    exit();
}

// Load Keycloak config
try {
    $config = include __DIR__ . '/keycloak-config.php';

    if (!isset($config['keycloak']) || !is_array($config['keycloak'])) {
        throw new RuntimeException('Invalid Keycloak configuration array.');
    }

    // Validate required configuration
    $required = ['clientId', 'redirectUri', 'authServerUrl', 'realm'];
    foreach ($required as $key) {
        if (empty($config['keycloak'][$key])) {
            throw new RuntimeException("Missing required configuration: $key");
        }
    }

    // Debug: Log configuration (without sensitive data)
    error_log('Keycloak config loaded: ' . json_encode([
        'clientId' => $config['keycloak']['clientId'],
        'redirectUri' => $config['keycloak']['redirectUri'],
        'authServerUrl' => $config['keycloak']['authServerUrl'],
        'realm' => $config['keycloak']['realm']
    ]));

    $provider = new KeycloakProvider($config['keycloak']);

} catch (Exception $e) {
    error_log('Keycloak config error: ' . $e->getMessage());
    http_response_code(500);
    exit('Configuration error. Contact the administrator.');
}

// OAuth Step 1: No code yet - redirect user
if (!isset($_GET['code'])) {
    try {
        // Clear any existing session data to prevent conflicts
        unset($_SESSION['oauth2state'], $_SESSION['oauth2_code_verifier'], $_SESSION['user']);
        
        // PKCE - Generate code verifier + challenge
        $codeVerifier = bin2hex(random_bytes(64));
        $_SESSION['oauth2_code_verifier'] = $codeVerifier;

        $codeChallenge = rtrim(strtr(
            base64_encode(hash('sha256', $codeVerifier, true)),
            '+/', '-_'), '=');

        // Generate authorization URL
        $authorizationUrl = $provider->getAuthorizationUrl([
            'code_challenge' => $codeChallenge,
            'code_challenge_method' => 'S256',
            'scope' => 'openid profile email'
        ]);

        // Save CSRF state
        $_SESSION['oauth2state'] = $provider->getState();

        // Debug log
        error_log('Redirecting to Keycloak: ' . $authorizationUrl);

        // Redirect to Keycloak login
        header('Location: ' . $authorizationUrl);
        exit;

    } catch (Exception $e) {
        error_log('Authorization URL error: ' . $e->getMessage());
        // Clear session data on error
        unset($_SESSION['oauth2state'], $_SESSION['oauth2_code_verifier']);
        http_response_code(500);
        echo '<h2>Error initiating login</h2>';
        echo '<p>Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<p><a href="index.php">Go back to home</a></p>';
        exit;
    }

} else {
    // OAuth Step 2: Handle redirect from Keycloak

    // Check state
    if (empty($_GET['state']) || $_GET['state'] !== ($_SESSION['oauth2state'] ?? null)) {
        unset($_SESSION['oauth2state']);
        http_response_code(400);
        exit('Invalid state - possible CSRF attempt.');
    }

    try {
        // Exchange code for token using PKCE verifier
        $accessToken = $provider->getAccessToken('authorization_code', [
            'code' => $_GET['code'],
            'code_verifier' => $_SESSION['oauth2_code_verifier'] ?? ''
        ]);

        // Store tokens
        $_SESSION['access_token'] = $accessToken->getToken();
        $_SESSION['id_token'] = $accessToken->getValues()['id_token'] ?? null;
        $_SESSION['refresh_token'] = $accessToken->getRefreshToken();
        $_SESSION['expires'] = $accessToken->getExpires();

        // Fetch user profile
        $user = $provider->getResourceOwner($accessToken);
        $_SESSION['user'] = $user->toArray();

        // Debug: Log successful authentication
        error_log('User authenticated successfully: ' . print_r($user->toArray(), true));

        // Cleanup PKCE/CSRF session data
        unset($_SESSION['oauth2state'], $_SESSION['oauth2_code_verifier']);

        // Redirect to homepage or dashboard
        header('Cache-Control: no-store');
        header('Location: home.php');
        exit;

    } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
        error_log('Token error: ' . $e->getMessage());
        http_response_code(401);
        exit('Authentication failed. Please login again.');
    } catch (Exception $e) {
        error_log('Unexpected error: ' . $e->getMessage());
        http_response_code(500);
        exit('Unexpected error. Please contact administrator.');
    }
}
