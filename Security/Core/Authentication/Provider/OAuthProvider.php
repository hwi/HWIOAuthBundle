<?php

namespace Knp\OAuthBundle\Security\Core\Authentication\Provider;

use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

use Knp\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;

use Buzz\Client\ClientInterface as HttpClientInterface,
    Buzz\Message\Request as HttpRequest,
    Buzz\Message\Response as HttpResponse;

class OAuthProvider implements AuthenticationProviderInterface
{
    private $oauthOptions = array();

    private $httpClient;

    private $userProvider;

    public function __construct(UserProviderInterface $userProvider, HttpClientInterface $httpClient, array $oauthOptions)
    {
        $this->userProvider = $userProvider;
        $this->httpClient   = $httpClient;
        $this->oauthOptions = $oauthOptions;
    }

    public function supports(TokenInterface $token)
    {
        return $token instanceof OAuthToken;
    }

    public function authenticate(TokenInterface $token)
    {
        $url = $this->oauthOptions['infos_url'].'?'.http_build_query(array(
            'access_token' => $token->getCredentials()
        ));

        $hRequest  = new HttpRequest(HttpRequest::METHOD_GET, $url);
        $hResponse = new HttpResponse();

        $this->httpClient->send($hRequest, $hResponse);

        $userInfos    = json_decode($hResponse->getContent(), true);
        $usernamePath = explode('.', $this->oauthOptions['username_path']);

        $username     = $userInfos;

        foreach ($usernamePath as $path)
        {
            $username = $username[$path];
        }

        $user = $this->userProvider->loadUserByUsername($username);

        $token = new OAuthToken($token->getCredentials(), $user->getRoles());
        $token->setUser($user);
        $token->setAuthenticated(true);

        return $token;
    }
}