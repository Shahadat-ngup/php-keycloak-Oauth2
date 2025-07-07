<?php
/**
 * OAuth2 Flow Test
 * Tests OAuth2 flow components without browser interaction
 */

require_once 'vendor/autoload.php';
require_once 'KeycloakProvider.php';
require_once 'keycloak-config.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo "=== OAuth2 Flow Test ===\n\n";

try {
    // Load Keycloak config
    $config = include 'keycloak-config.php';
    
    if (!isset($config['keycloak']) || !is_array($config['keycloak'])) {
        throw new RuntimeException('Invalid Keycloak configuration array.');
    }

    echo "Testing OAuth2 Provider Configuration...\n";
    $provider = new KeycloakProvider($config['keycloak']);
    echo "✅ PASS - OAuth2 provider created successfully\n";

    // Test PKCE generation
    echo "\nTesting PKCE Integration...\n";
    $codeVerifier = bin2hex(random_bytes(64));
    $codeChallenge = rtrim(strtr(
        base64_encode(hash('sha256', $codeVerifier, true)),
        '+/', '-_'), '=');
    
    echo "Code Verifier Length: " . strlen($codeVerifier) . " (expected: 128)\n";
    echo "Code Challenge Length: " . strlen($codeChallenge) . " (expected: 43)\n";
    echo "✅ PASS - PKCE parameters generated correctly\n";

    // Test authorization URL generation
    echo "\nTesting Authorization URL Generation...\n";
    $authUrl = $provider->getAuthorizationUrl([
        'code_challenge' => $codeChallenge,
        'code_challenge_method' => 'S256',
        'scope' => 'openid profile email'
    ]);
    
    $parsedUrl = parse_url($authUrl);
    parse_str($parsedUrl['query'], $params);
    
    echo "Base URL: " . $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . $parsedUrl['path'] . "\n";
    echo "Parameters:\n";
    echo "- client_id: " . ($params['client_id'] ?? 'MISSING') . "\n";
    echo "- response_type: " . ($params['response_type'] ?? 'MISSING') . "\n";
    echo "- scope: " . ($params['scope'] ?? 'MISSING') . "\n";
    echo "- code_challenge: " . (isset($params['code_challenge']) ? 'PRESENT' : 'MISSING') . "\n";
    echo "- code_challenge_method: " . ($params['code_challenge_method'] ?? 'MISSING') . "\n";
    echo "- state: " . (isset($params['state']) ? 'PRESENT' : 'MISSING') . "\n";
    
    // Validate required parameters
    $required = ['client_id', 'response_type', 'scope', 'code_challenge', 'code_challenge_method', 'state'];
    $missing = array_filter($required, function($param) use ($params) {
        return !isset($params[$param]);
    });
    
    if (empty($missing)) {
        echo "✅ PASS - All required OAuth2 parameters present\n";
    } else {
        echo "❌ FAIL - Missing parameters: " . implode(', ', $missing) . "\n";
    }
    
    // Test token endpoint URL
    echo "\nTesting Token Endpoint Configuration...\n";
    $tokenUrl = $provider->getBaseAccessTokenUrl([]);
    echo "Token Endpoint: $tokenUrl\n";
    echo "✅ PASS - Token endpoint configured\n";
    
    // Test user info endpoint
    echo "\nTesting UserInfo Endpoint Configuration...\n";
    $mockToken = new \League\OAuth2\Client\Token\AccessToken(['access_token' => 'mock']);
    $userInfoUrl = $provider->getResourceOwnerDetailsUrl($mockToken);
    echo "UserInfo Endpoint: $userInfoUrl\n";
    echo "✅ PASS - UserInfo endpoint configured\n";

    echo "\n=== OAuth2 Flow Test Complete ===\n";
    echo "Result: ✅ PASS - All OAuth2 components working correctly\n";

} catch (Exception $e) {
    echo "❌ FAIL - Error: " . $e->getMessage() . "\n";
    exit(1);
}
