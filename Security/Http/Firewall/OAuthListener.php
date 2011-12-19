<?php

namespace Knp\OauthBundle\Security\Http\Firewall;

use Symfony\Component\Security\Http\Firewall\AbstractAuthenticationListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

use Knp\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use Knp\OAuthBundle\Security\Http\OAuth\OAuthProviderInterface;

class OAuthListener extends AbstractAuthenticationListener
{
    private $oauthProvider;

    public function setOAuthProvider(OAuthProviderInterface $oauthProvider)
    {
        $this->oauthProvider = $oauthProvider;
    }

    protected function attemptAuthentication(Request $request)
    {
        $accessToken = $this->oauthProvider->getAccessToken($request->get('code'));

        $token = new OAuthToken($accessToken);

        return $this->authenticationManager->authenticate($token);
    }
}