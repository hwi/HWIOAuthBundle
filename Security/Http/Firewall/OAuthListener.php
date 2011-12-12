<?php

namespace Knp\OauthBundle\Security\Http\Firewall;

use Symfony\Component\Security\Http\Firewall\AbstractAuthenticationListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Knp\OAuthBundle\Security\Core\Token\OAuthToken;

class OAuthListener extends AbstractAuthenticationListener
{
    protected function attemptAuthentication(Request $request)
    {
        var_dump($request->get('code')); die;
    }
}