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
   KEYCLOAK_REDIRECT_URI=http://localhost:8080/login.php
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
- **Valid redirect URIs**: `http://localhost:8080/*`
- **Valid post logout redirect URIs**: `http://localhost:8080/index.php/*`
- **Web origins**: `http://localhost:8080/*`
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
   # Using PHP built-in server (try different ports if 8000 is in use)
   php -S localhost:8080
   
   # Or if port 8080 is in use, try another port
   php -S localhost:3000
   
   # Or configure Apache/Nginx to serve the project
   ```

2. **Access the application**
   - Navigate to `http://localhost:8080` (or the port you're using)
   - Click "Login with Keycloak"
   - Authenticate with your Keycloak credentials
   - You'll be redirected to the home page

3. **User Flow**
   ```
   index.php → login.php → Keycloak → login.php (callback) → home.php
   ```

## File Descriptions

### `KeycloakProvider.php`
Custom OAuth2 provider extending League's AbstractProvider for Keycloak integration.

### `login.php`
Main authentication handler that manages OAuth2 flow with PKCE.

### `keycloak-config.php`
Configuration file that loads environment variables.

### `home.php`
Protected dashboard showing authenticated user information.

### `logout.php`
Secure logout implementation with Keycloak session cleanup.

## PKCE Testing

### Test Files for PKCE Verification

Two test files are included to verify the PKCE implementation:

#### 1. `test-pkce.php`
**Purpose**: Comprehensive PKCE and Keycloak configuration testing
- Tests PKCE code generation and verification algorithms
- Validates environment configuration
- Tests Keycloak server connectivity and PKCE support
- Generates complete authorization URLs with PKCE parameters
- **Run**: `php test-pkce.php`

#### 2. `test-oauth-flow.php`
**Purpose**: Tests OAuth2 flow components without external dependencies
- Validates OAuth2 provider configuration
- Tests authorization URL generation with PKCE parameters
- Verifies token and userinfo endpoint configuration
- **Run**: `php test-oauth-flow.php`

### Manual PKCE Verification

Use browser developer tools to verify PKCE in the actual flow:
1. Open Developer Tools (F12) → Network tab
2. Start login process
3. Check authorization request for `code_challenge` and `code_challenge_method=S256`
4. Check token exchange request for `code_verifier` parameter

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
