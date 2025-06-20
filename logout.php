<?php
require_once 'vendor/autoload.php';
require_once 'KeycloakProvider.php';
require_once 'keycloak-config.php';


$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

session_start();

$config = include 'keycloak-config.php';

// 1. First clear the local session
$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();

// 2. Get the ID token from session if available
$id_token = $_SESSION['id_token'] ?? null;

// 3. Build the proper Keycloak logout URL
$logoutParams = [
    'client_id' => $config['keycloak']['clientId'],
    'post_logout_redirect_uri' => 'http://localhost:8000/index.php'
];

// Add id_token_hint if available (makes logout immediate)
if ($id_token) {
    $logoutParams['id_token_hint'] = $id_token;
}

$logoutUrl = $config['keycloak']['authServerUrl'] . '/realms/' . $config['keycloak']['realm'] . 
             '/protocol/openid-connect/logout?' . http_build_query($logoutParams);

// 4. Force redirect
header('Location: ' . $logoutUrl);
exit;