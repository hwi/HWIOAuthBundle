<?php

/*
 * This file is part of the KnpOAuthBundle package.
 *
 * (c) KnpLabs <hello@knplabs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Knp\Bundle\OauthBundle\Security\Http\Firewall;

use Symfony\Component\Security\Http\Firewall\AbstractAuthenticationListener,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\Security\Core\Exception\AuthenticationException;

use Knp\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken,
    Knp\Bundle\OAuthBundle\Security\Http\OAuth\OAuthProviderInterface;

/**
 * OAuthListener
 *
 * @author Geoffrey Bachelet <geoffrey.bachelet@gmail.com>
 */
class OAuthListener extends AbstractAuthenticationListener
{
    /**
     * @var Knp\Bundle\OAuthBundle\Security\Http\OAuth\OAuthProviderInterface
     */
    private $oauthProvider;

    /**
     * @var Knp\Bundle\OAuthBundle\Security\Http\OAuth\OAuthProviderInterface $oauthProvider
     */
    public function setOAuthProvider(OAuthProviderInterface $oauthProvider)
    {
        $this->oauthProvider = $oauthProvider;
    }

    /**
     * {@inheritDoc}
     */
    protected function attemptAuthentication(Request $request)
    {
        $accessToken = $this->oauthProvider->getAccessToken($request);

        $token = new OAuthToken($accessToken);

        return $this->authenticationManager->authenticate($token);
    }
}