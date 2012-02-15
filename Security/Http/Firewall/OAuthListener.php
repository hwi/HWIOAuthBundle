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


use Knp\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface,
    Knp\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;

/**
 * OAuthListener
 *
 * @author Geoffrey Bachelet <geoffrey.bachelet@gmail.com>
 * @author Alexander <iam.asm89@gmail.com>
 */
class OAuthListener extends AbstractAuthenticationListener
{
    /**
     * @var Knp\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface
     */
    private $resourceOwner;

    private $checkPath;

    /**
     * @var Knp\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface $resourceOwner
     */
    public function setResourceOwner(ResourceOwnerInterface $resourceOwner)
    {
        $this->resourceOwner = $resourceOwner;
    }

    public function setCheckPath($checkPath)
    {
        $this->checkPath = $checkPath;
    }

    /**
     * {@inheritDoc}
     */
    protected function attemptAuthentication(Request $request)
    {
        $accessToken = $this->resourceOwner->getAccessToken(
            $request->query->get('code'),
            $this->httpUtils->createRequest($request, $this->checkPath)->getUri()
        );

        $token = new OAuthToken($accessToken);

        return $this->authenticationManager->authenticate($token);
    }
}
