<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OauthBundle\Security\Http\Firewall;

use Symfony\Component\Security\Http\Firewall\AbstractAuthenticationListener,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\Security\Core\Exception\AuthenticationException;


use HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface,
    HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;

/**
 * OAuthListener
 *
 * @author Geoffrey Bachelet <geoffrey.bachelet@gmail.com>
 * @author Alexander <iam.asm89@gmail.com>
 */
class OAuthListener extends AbstractAuthenticationListener
{
    /**
     * @var HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface
     */
    private $resourceOwner;

    private $checkPath;

    /**
     * @var HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface $resourceOwner
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
