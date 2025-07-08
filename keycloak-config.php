<?php

return [
    'keycloak' => [
        'clientId'       => $_ENV['KEYCLOAK_CLIENT_ID'] ?? '',
        // For PKCE, don't include clientSecret if it's empty (public client)
        'clientSecret'   => !empty($_ENV['KEYCLOAK_CLIENT_SECRET']) ? $_ENV['KEYCLOAK_CLIENT_SECRET'] : null,
        'redirectUri'    => $_ENV['KEYCLOAK_REDIRECT_URI'] ?? '',
        'authServerUrl'  => $_ENV['KEYCLOAK_AUTH_SERVER_URL'] ?? '',
        'realm'          => $_ENV['KEYCLOAK_REALM'] ?? '',
        'scope'          => ['openid', 'profile', 'email'],
    ]
];
