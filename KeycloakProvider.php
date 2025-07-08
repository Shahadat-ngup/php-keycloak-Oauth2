<?php
require_once 'vendor/autoload.php';

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

class KeycloakProvider extends AbstractProvider
{
    use BearerAuthorizationTrait;

    protected $authServerUrl;
    protected $realm;
    protected $baseUrl;

    public function __construct(array $options = [], array $collaborators = [])
    {
        // Filter out null clientSecret for PKCE public clients
        if (isset($options['clientSecret']) && $options['clientSecret'] === null) {
            unset($options['clientSecret']);
        }
        
        parent::__construct($options, $collaborators);
        
        $this->authServerUrl = rtrim($options['authServerUrl'], '/');
        $this->realm = $options['realm'];
        $this->baseUrl = $this->authServerUrl . '/realms/' . $this->realm;
    }

    public function getBaseAuthorizationUrl()
    {
        return $this->authServerUrl . '/realms/' . $this->realm . '/protocol/openid-connect/auth';
    }

    public function getBaseAccessTokenUrl(array $params)
    {
        return $this->baseUrl . '/protocol/openid-connect/token';
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return $this->baseUrl . '/protocol/openid-connect/userinfo';
    }

    protected function getDefaultScopes()
    {
        return ['openid', 'profile', 'email'];
    }

    protected function checkResponse(ResponseInterface $response, $data)
    {
        if (!empty($data['error'])) {
            $error = $data['error'];
            $message = $data['error_description'] ?? $error;
            throw new IdentityProviderException($message, $response->getStatusCode(), $data);
        }
    }

    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new KeycloakResourceOwner($response);
    }

    protected function getScopeSeparator()
    {
        return ' ';
    }

    /**
     * Override to support PKCE (no client secret authentication)
     */
    protected function getAccessTokenOptions(array $params)
    {
        $options = parent::getAccessTokenOptions($params);
        
        // For PKCE, we use client_id in the body instead of basic auth
        if (!isset($this->clientSecret) || $this->clientSecret === null) {
            // Remove basic auth headers for public clients
            if (isset($options['headers']['Authorization'])) {
                unset($options['headers']['Authorization']);
            }
            
            // Ensure client_id is in the body for public clients
            $options['body']['client_id'] = $this->clientId;
        }
        
        return $options;
    }
}

class KeycloakResourceOwner
{
    private $response;

    public function __construct(array $response)
    {
        $this->response = $response;
    }

    public function getId()
    {
        return $this->response['sub'] ?? null;
    }

    public function getName()
    {
        return $this->response['name'] ?? null;
    }

    public function getEmail()
    {
        return $this->response['email'] ?? null;
    }

    public function toArray()
    {
        return $this->response;
    }
}