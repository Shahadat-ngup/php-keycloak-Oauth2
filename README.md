# PHP Keycloak OAuth2 with PKCE Implementation

A secure PHP implementation of Keycloak authentication using OAuth2 Authorization Code flow with PKCE (Proof Key for Code Exchange). This project demonstrates best practices for integrating Keycloak authentication in PHP applications.

## Features

- 🔐 **OAuth2 Authorization Code Flow** with PKCE for enhanced security
- 🛡️ **CSRF Protection** using state parameter validation
- 🔄 **Session Management** with secure cookie configuration
- 🚀 **Production Ready** with environment-based configuration
- 📱 **User Profile Management** with Keycloak userinfo endpoint
- 🔓 **Secure Logout** with proper session cleanup and Keycloak logout

## Project Structure

```
PHP_Keycloak/
├── composer.json              # Dependencies configuration
├── composer.lock              # Lock file for dependencies
├── keycloak-config.php        # Keycloak configuration
├── KeycloakProvider.php       # Custom OAuth2 provider for Keycloak
├── index.php                  # Landing page with login button
├── login.php                  # OAuth2 login handler with PKCE
├── callback.php               # OAuth2 callback handler (redirects to login.php)
├── home.php                   # Protected dashboard page
├── logout.php                 # Logout handler
├── .env                       # Environment variables (create this - not in git)
├── .gitignore                 # Git ignore file to exclude sensitive files
└── vendor/                    # Composer dependencies
```

## Requirements

- PHP 7.4 or higher
- Composer
- Keycloak server
- Web server (Apache/Nginx) or PHP built-in server

## Installation

1. **Clone or download the project**
   ```bash
   cd /path/to/your/project
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Create environment file**
   Create a `.env` file in the project root:
   ```env
   # Application environment
   APP_ENV=development

   # Keycloak Configuration
   KEYCLOAK_AUTH_SERVER_URL=http://localhost:8080
   KEYCLOAK_REALM=your-realm-name
   KEYCLOAK_CLIENT_ID=your-client-id
   KEYCLOAK_CLIENT_SECRET=your-client-secret
   KEYCLOAK_REDIRECT_URI=http://localhost:8000/login.php
   ```
   **⚠️ Important**: The `.env` file contains sensitive information and is excluded from git via `.gitignore`. Never commit this file to version control.
Note: The values have to be taken from Keycloak Client and make sure PKCE is active inside the client in the Advanced tab
## Keycloak Setup

### 1. Create a Client
1. Navigate to **Clients** in your realm
2. Click **Create Client**
3. Configure the client:
   - **Client ID**: `php-keycloak-app` (or your preferred name)
   - **Client Type**: `OpenID Connect`
   - **Client authentication**: `On` (if you want to use client secret)

### 3. Configure Client Settings
In the client settings:
- **Root URL**: 'https://keycloak.ccom.ipb.pt:8443'
- **Valid redirect URIs**: `http://localhost:8000/*`
- **Valid post logout redirect URIs**: `http://localhost:8000/index.php/*`
- **Web origins**: `http://localhost:8000/*`
- **Standard Flow Enabled**: `On`
- **Direct Access Grants Enabled**: `On`
Note: The * allows all the remaining paths
### 4. PKCE Configuration
- **Proof Key for Code Exchange Code Challenge Method**: `S256`
- This enables PKCE support for enhanced security

### 5. Get Client Credentials
- Copy the **Client ID**
- Go to **Credentials** tab and copy the **Client Secret**
- Update your `.env` file with these values

## How It Works

### OAuth2 Authorization Code Flow with PKCE

1. **User Initiates Login** (`index.php`)
   - User clicks "Login with Keycloak"
   - Redirected to `login.php`

2. **PKCE Code Generation** (`login.php`)
   ```php
   // Generate PKCE code verifier (random 64-byte string)
   $codeVerifier = bin2hex(random_bytes(64));
   
   // Generate code challenge (SHA256 hash of verifier, base64url encoded)
   $codeChallenge = rtrim(strtr(
       base64_encode(hash('sha256', $codeVerifier, true)),
       '+/', '-_'), '=');
   ```

3. **Authorization Request**
   - User redirected to Keycloak with authorization URL
   - Includes PKCE code challenge and state for CSRF protection

4. **User Authentication**
   - User authenticates with Keycloak
   - Keycloak redirects back with authorization code

5. **Token Exchange**
   - Exchange authorization code for access token
   - Include PKCE code verifier for verification
   - Store tokens and user info in session

6. **Protected Access**
   - User can access protected pages (`home.php`)
   - Session contains user profile and tokens

### Security Features

- **PKCE (Proof Key for Code Exchange)**: Prevents authorization code interception attacks
- **CSRF Protection**: State parameter validates the request authenticity
- **Secure Sessions**: HTTPOnly, Secure, and SameSite cookie attributes
- **Environment Variables**: Sensitive configuration stored in `.env`
- **HTTPS Enforcement**: Automatic redirect to HTTPS in production

## Usage

1. **Start your web server**
   ```bash
   # Using PHP built-in server
   php -S localhost:8000
   
   # Or configure Apache/Nginx to serve the project
   ```

2. **Access the application**
   - Navigate to `http://localhost:8000`
   - Click "Login with Keycloak"
   - Authenticate with your Keycloak credentials
   - You'll be redirected to the home page

3. **User Flow**
   ```
   index.php → login.php → Keycloak → login.php (callback) → home.php
   ```

## File Descriptions

### `KeycloakProvider.php`
Custom OAuth2 provider extending League's AbstractProvider:
- Implements Keycloak-specific endpoints
- Handles token exchange and user info retrieval
- Provides resource owner (user) object

### `login.php`
Main authentication handler:
- Generates PKCE code verifier/challenge
- Handles authorization redirect
- Processes OAuth2 callback
- Manages token exchange and user session

### `keycloak-config.php`
Configuration file that loads environment variables:
- Client credentials
- Keycloak server URLs
- Redirect URIs

### `home.php`
Protected dashboard showing user information:
- Displays user name and email
- Provides logout functionality

### `logout.php`
Secure logout implementation:
- Clears local session
- Redirects to Keycloak logout endpoint
- Includes ID token hint for immediate logout

## PKCE Testing

### How to Test PKCE Implementation

PKCE (Proof Key for Code Exchange) adds an extra layer of security to the OAuth2 flow. Here's how to verify it's working:

#### 1. **Verify PKCE Parameters in Network Traffic**

Use browser developer tools to monitor the authentication flow:

1. Open browser Developer Tools (F12)
2. Go to **Network** tab
3. Start the login process
4. Look for the initial authorization request to Keycloak

**Check for PKCE parameters:**
```
https://your-keycloak.com/realms/your-realm/protocol/openid-connect/auth?
  client_id=your-client-id&
  redirect_uri=http://localhost:8000/login.php&
  response_type=code&
  scope=openid+profile+email&
  state=random-state-value&
  code_challenge=base64url-encoded-challenge&  ← PKCE Challenge
  code_challenge_method=S256                    ← PKCE Method
```

#### 2. **Verify Code Verifier in Token Exchange**

In the token exchange request (second network call), verify:
```
POST /realms/your-realm/protocol/openid-connect/token
Content-Type: application/x-www-form-urlencoded

grant_type=authorization_code&
code=authorization-code&
client_id=your-client-id&
client_secret=your-client-secret&
redirect_uri=http://localhost:8000/login.php&
code_verifier=original-random-verifier  ← PKCE Verifier
```

#### 3. **Test PKCE Failure Scenarios**

To verify PKCE is working, try these intentional failures:

**A. Modify Code Verifier (Simulate Attack)**
1. Add temporary debug code in `login.php`:
   ```php
   // Before token exchange, modify the verifier
   $_SESSION['oauth2_code_verifier'] = 'wrong_verifier';
   ```
2. Complete the login flow
3. Should receive error: "Invalid code verifier"

**B. Remove PKCE Parameters**
1. Comment out PKCE generation in `login.php`:
   ```php
   // $authorizationUrl = $provider->getAuthorizationUrl([
   //     'code_challenge' => $codeChallenge,
   //     'code_challenge_method' => 'S256',
   //     'scope' => 'openid profile email'
   // ]);
   ```
2. If Keycloak requires PKCE, login should fail

#### 4. **PKCE Security Test Script**

Create a test script to verify PKCE generation:

```php
<?php
// test-pkce.php
function testPKCE() {
    // Generate code verifier
    $codeVerifier = bin2hex(random_bytes(64));
    echo "Code Verifier Length: " . strlen($codeVerifier) . " (should be 128)\n";
    echo "Code Verifier: " . $codeVerifier . "\n\n";
    
    // Generate code challenge
    $codeChallenge = rtrim(strtr(
        base64_encode(hash('sha256', $codeVerifier, true)),
        '+/', '-_'), '=');
    echo "Code Challenge Length: " . strlen($codeChallenge) . " (should be 43)\n";
    echo "Code Challenge: " . $codeChallenge . "\n\n";
    
    // Verify challenge can be reproduced from verifier
    $verifyChallenge = rtrim(strtr(
        base64_encode(hash('sha256', $codeVerifier, true)),
        '+/', '-_'), '=');
    
    echo "PKCE Verification: " . ($codeChallenge === $verifyChallenge ? "✅ PASS" : "❌ FAIL") . "\n";
}

testPKCE();
```

Run: `php test-pkce.php`

#### 5. **Keycloak Server-Side Verification**

Check Keycloak logs for PKCE validation:
```bash
# If using Docker
docker logs keycloak-container-name | grep -i pkce

# Look for entries like:
# "PKCE code challenge verified successfully"
# "Invalid PKCE code verifier"
```

#### 6. **Manual PKCE Flow Test**

Test the complete flow manually:

1. **Generate PKCE parameters:**
   ```bash
   # Generate code verifier (128 hex chars = 64 random bytes)
   CODE_VERIFIER=$(openssl rand -hex 64)
   echo "Code Verifier: $CODE_VERIFIER"
   
   # Generate code challenge (SHA256 hash, base64url encoded)
   CODE_CHALLENGE=$(echo -n $CODE_VERIFIER | sha256sum | cut -d' ' -f1 | xxd -r -p | base64 | tr '+/' '-_' | tr -d '=')
   echo "Code Challenge: $CODE_CHALLENGE"
   ```

2. **Test authorization URL:**
   ```bash
   curl -v "http://localhost:8080/realms/your-realm/protocol/openid-connect/auth?client_id=your-client&response_type=code&redirect_uri=http://localhost:8000/login.php&scope=openid&code_challenge=$CODE_CHALLENGE&code_challenge_method=S256"
   ```

#### Expected PKCE Behavior:
- ✅ **Success**: Login completes when correct verifier is used
- ❌ **Failure**: Error when wrong verifier is provided
- ❌ **Failure**: Error when PKCE parameters are missing (if required)

### PKCE Benefits Demonstrated:
1. **No Client Secret in Public Clients**: PKCE allows secure OAuth2 for SPAs/mobile apps
2. **Code Interception Protection**: Even if authorization code is intercepted, it's useless without the verifier
3. **Dynamic Security**: Each login generates unique verifier/challenge pair

## Troubleshooting

### Common Issues

1. **"Invalid redirect URI"**
   - Ensure redirect URI in Keycloak client matches your `.env` file
   - Check for trailing slashes and exact URL matching

2. **"Invalid client credentials"**
   - Verify client ID and secret in `.env`
   - Ensure client authentication is enabled in Keycloak

3. **"PKCE validation failed"**
   - Check if PKCE is required in Keycloak client settings
   - Verify code challenge method is set to S256

4. **Session issues**
   - Clear browser cookies and session data
   - Check PHP session configuration

### Debug Mode

Add debug information to `login.php`:
```php
// Add after token exchange
error_log('Access Token: ' . $accessToken->getToken());
error_log('User Info: ' . print_r($user->toArray(), true));
```

## Security Considerations

- Always use HTTPS in production
- Implement proper session timeout
- Consider implementing token refresh logic
- Use secure session storage
- Validate all input parameters
- Implement proper error handling
- Use strong random number generation for PKCE
- **Never commit sensitive files**: `.env` file is excluded via `.gitignore`
- Use environment-specific configuration files for different deployments
- Regularly rotate client secrets and access tokens

## Contributing

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request


## References

- [RFC 7636 - Proof Key for Code Exchange](https://tools.ietf.org/html/rfc7636)
- [Keycloak Documentation](https://www.keycloak.org/documentation)
- [League OAuth2 Client](https://oauth2-client.thephpleague.com/)
- [OAuth 2.0 Security Best Practices](https://tools.ietf.org/html/draft-ietf-oauth-security-topics)
