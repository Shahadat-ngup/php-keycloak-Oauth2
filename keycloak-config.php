<?php

return [
    'keycloak' => [
        'clientId'       => $_ENV['KEYCLOAK_CLIENT_ID'] ?? '',
        'clientSecret'   => $_ENV['KEYCLOAK_CLIENT_SECRET'] ?? '',
        'redirectUri'    => $_ENV['KEYCLOAK_REDIRECT_URI'] ?? '',
        'authServerUrl'  => $_ENV['KEYCLOAK_AUTH_SERVER_URL'] ?? '',
        'realm'          => $_ENV['KEYCLOAK_REALM'] ?? '',
        'scope'          => ['openid', 'profile', 'email'],
    ]
];
