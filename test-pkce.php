<?php
/**
 * PKCE (Proof Key for Code Exchange) Test Script
 * This script tests the PKCE implementation to ensure it's working correctly
 */

echo "=== PKCE (Proof Key for Code Exchange) Test Script ===\n\n";

function testPKCE() {
    echo "🔧 Testing PKCE Code Generation...\n";
    echo str_repeat("-", 50) . "\n";
    
    // Test 1: Generate code verifier
    echo "1. Generating PKCE Code Verifier:\n";
    $codeVerifier = bin2hex(random_bytes(64));
    $verifierLength = strlen($codeVerifier);
    
    echo "   Code Verifier: " . substr($codeVerifier, 0, 32) . "...\n";
    echo "   Length: $verifierLength characters\n";
    echo "   Expected: 128 characters\n";
    echo "   Result: " . ($verifierLength === 128 ? "✅ PASS" : "❌ FAIL") . "\n\n";
    
    // Test 2: Generate code challenge
    echo "2. Generating PKCE Code Challenge:\n";
    $codeChallenge = rtrim(strtr(
        base64_encode(hash('sha256', $codeVerifier, true)),
        '+/', '-_'), '=');
    $challengeLength = strlen($codeChallenge);
    
    echo "   Code Challenge: $codeChallenge\n";
    echo "   Length: $challengeLength characters\n";
    echo "   Expected: 43 characters\n";
    echo "   Result: " . ($challengeLength === 43 ? "✅ PASS" : "❌ FAIL") . "\n\n";
    
    // Test 3: Verify challenge can be reproduced from verifier
    echo "3. Testing PKCE Verification:\n";
    $verifyChallenge = rtrim(strtr(
        base64_encode(hash('sha256', $codeVerifier, true)),
        '+/', '-_'), '=');
    
    echo "   Original Challenge: $codeChallenge\n";
    echo "   Reproduced Challenge: $verifyChallenge\n";
    echo "   Match: " . ($codeChallenge === $verifyChallenge ? "✅ PASS" : "❌ FAIL") . "\n\n";
    
    // Test 4: Test multiple generations (uniqueness)
    echo "4. Testing PKCE Uniqueness:\n";
    $challenges = [];
    for ($i = 0; $i < 5; $i++) {
        $verifier = bin2hex(random_bytes(64));
        $challenge = rtrim(strtr(
            base64_encode(hash('sha256', $verifier, true)),
            '+/', '-_'), '=');
        $challenges[] = $challenge;
        echo "   Challenge $i: " . substr($challenge, 0, 20) . "...\n";
    }
    
    $unique = count($challenges) === count(array_unique($challenges));
    echo "   All challenges unique: " . ($unique ? "✅ PASS" : "❌ FAIL") . "\n\n";
    
    // Test 5: Test base64url encoding
    echo "5. Testing Base64URL Encoding:\n";
    $hasInvalidChars = preg_match('/[+\/=]/', $codeChallenge);
    echo "   Challenge contains +, /, or =: " . ($hasInvalidChars ? "❌ FAIL" : "✅ PASS") . "\n";
    echo "   Valid Base64URL: " . (!$hasInvalidChars ? "✅ PASS" : "❌ FAIL") . "\n\n";
    
    return [
        'verifier' => $codeVerifier,
        'challenge' => $codeChallenge,
        'tests_passed' => $verifierLength === 128 && $challengeLength === 43 && 
                         $codeChallenge === $verifyChallenge && $unique && !$hasInvalidChars
    ];
}

function testKeycloakConnection() {
    echo "🌐 Testing Keycloak Connection...\n";
    echo str_repeat("-", 50) . "\n";
    
    // Load environment variables
    if (!file_exists('.env')) {
        echo "❌ .env file not found!\n";
        return false;
    }
    
    $env = parse_ini_file('.env');
    $authServerUrl = $env['KEYCLOAK_AUTH_SERVER_URL'] ?? '';
    $realm = $env['KEYCLOAK_REALM'] ?? '';
    
    if (empty($authServerUrl) || empty($realm)) {
        echo "❌ Keycloak configuration missing in .env\n";
        return false;
    }
    
    echo "   Auth Server: $authServerUrl\n";
    echo "   Realm: $realm\n";
    
    // Test Keycloak endpoint accessibility
    $wellKnownUrl = $authServerUrl . '/realms/' . $realm . '/.well-known/openid_configuration';
    echo "   Testing: $wellKnownUrl\n";
    
    $context = stream_context_create([
        'http' => [
            'timeout' => 10,
            'method' => 'GET'
        ],
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false
        ]
    ]);
    
    $response = @file_get_contents($wellKnownUrl, false, $context);
    
    if ($response === false) {
        echo "   Connection: ❌ FAIL - Cannot reach Keycloak server\n";
        return false;
    }
    
    $config = json_decode($response, true);
    if (!$config) {
        echo "   Response: ❌ FAIL - Invalid JSON response\n";
        return false;
    }
    
    echo "   Connection: ✅ PASS - Keycloak server accessible\n";
    echo "   Issuer: " . ($config['issuer'] ?? 'Unknown') . "\n";
    echo "   Authorization Endpoint: " . (isset($config['authorization_endpoint']) ? "✅ Found" : "❌ Missing") . "\n";
    echo "   Token Endpoint: " . (isset($config['token_endpoint']) ? "✅ Found" : "❌ Missing") . "\n";
    echo "   Userinfo Endpoint: " . (isset($config['userinfo_endpoint']) ? "✅ Found" : "❌ Missing") . "\n";
    
    // Check PKCE support
    $pkceSupported = isset($config['code_challenge_methods_supported']) && 
                     in_array('S256', $config['code_challenge_methods_supported']);
    echo "   PKCE S256 Support: " . ($pkceSupported ? "✅ PASS" : "❌ FAIL") . "\n\n";
    
    return true;
}

function testConfiguration() {
    echo "⚙️  Testing Configuration...\n";
    echo str_repeat("-", 50) . "\n";
    
    if (!file_exists('.env')) {
        echo "❌ .env file not found!\n";
        return false;
    }
    
    $env = parse_ini_file('.env');
    $required = [
        'KEYCLOAK_CLIENT_ID',
        'KEYCLOAK_CLIENT_SECRET', 
        'KEYCLOAK_REDIRECT_URI',
        'KEYCLOAK_AUTH_SERVER_URL',
        'KEYCLOAK_REALM'
    ];
    
    $allPresent = true;
    foreach ($required as $key) {
        $present = !empty($env[$key]);
        echo "   $key: " . ($present ? "✅ Set" : "❌ Missing") . "\n";
        if (!$present) $allPresent = false;
    }
    
    // Check redirect URI format
    $redirectUri = $env['KEYCLOAK_REDIRECT_URI'] ?? '';
    $validRedirect = filter_var($redirectUri, FILTER_VALIDATE_URL) && 
                     strpos($redirectUri, 'login.php') !== false;
    echo "   Redirect URI Format: " . ($validRedirect ? "✅ Valid" : "❌ Invalid") . "\n";
    
    // Check if port matches current server
    $currentPort = "8080"; // Assuming we're running on 8080
    $portMatches = strpos($redirectUri, ":$currentPort") !== false;
    echo "   Port Matches Server: " . ($portMatches ? "✅ Yes" : "⚠️  Check port") . "\n\n";
    
    return $allPresent && $validRedirect;
}

function generateAuthorizationUrl($verifier, $challenge) {
    echo "🔗 Generating Test Authorization URL...\n";
    echo str_repeat("-", 50) . "\n";
    
    $env = parse_ini_file('.env');
    
    $authUrl = $env['KEYCLOAK_AUTH_SERVER_URL'] . '/realms/' . $env['KEYCLOAK_REALM'] . '/protocol/openid-connect/auth';
    
    $params = [
        'client_id' => $env['KEYCLOAK_CLIENT_ID'],
        'redirect_uri' => $env['KEYCLOAK_REDIRECT_URI'],
        'response_type' => 'code',
        'scope' => 'openid profile email',
        'state' => bin2hex(random_bytes(16)),
        'code_challenge' => $challenge,
        'code_challenge_method' => 'S256'
    ];
    
    $fullUrl = $authUrl . '?' . http_build_query($params);
    
    echo "   Base URL: $authUrl\n";
    echo "   Parameters:\n";
    foreach ($params as $key => $value) {
        $displayValue = strlen($value) > 50 ? substr($value, 0, 47) . '...' : $value;
        echo "     $key: $displayValue\n";
    }
    
    echo "\n   Full Authorization URL:\n";
    echo "   " . substr($fullUrl, 0, 80) . "...\n\n";
    
    return $fullUrl;
}

// Run all tests
echo "Starting PKCE and Keycloak Integration Tests...\n";
echo str_repeat("=", 60) . "\n\n";

// Test 1: PKCE Implementation
$pkceResult = testPKCE();

// Test 2: Configuration
$configResult = testConfiguration();

// Test 3: Keycloak Connection  
$connectionResult = testKeycloakConnection();

// Test 4: Generate test URL
if ($pkceResult['tests_passed'] && $configResult) {
    $authUrl = generateAuthorizationUrl($pkceResult['verifier'], $pkceResult['challenge']);
}

// Summary
echo "📊 Test Summary:\n";
echo str_repeat("=", 60) . "\n";
echo "PKCE Implementation: " . ($pkceResult['tests_passed'] ? "✅ PASS" : "❌ FAIL") . "\n";
echo "Configuration: " . ($configResult ? "✅ PASS" : "❌ FAIL") . "\n";
echo "Keycloak Connection: " . ($connectionResult ? "✅ PASS" : "❌ FAIL") . "\n";

$overallResult = $pkceResult['tests_passed'] && $configResult && $connectionResult;
echo "\nOverall Result: " . ($overallResult ? "✅ ALL TESTS PASSED" : "❌ SOME TESTS FAILED") . "\n";

if ($overallResult) {
    echo "\n🎉 Your PKCE implementation is working correctly!\n";
    echo "You can now test the full authentication flow in your browser.\n";
} else {
    echo "\n⚠️  Please fix the failing tests before proceeding.\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
