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