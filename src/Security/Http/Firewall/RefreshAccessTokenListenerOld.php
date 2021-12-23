<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Security\Http\Firewall;

use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;

class RefreshAccessTokenListenerOld extends RefreshAccessTokenListener
{
    private AuthenticationProviderInterface $oAuthProvider;

    public function __construct(
        AuthenticationProviderInterface $oAuthProvider
    ) {
        $this->oAuthProvider = $oAuthProvider;
    }

    /**
     * @template T of OAuthToken
     *
     * @param T $token
     *
     * @return T
     */
    protected function refreshToken(OAuthToken $token): OAuthToken
    {
        // @phpstan-ignore-next-line returns TokenInterface instead of OAuthToken
        return $this->oAuthProvider->authenticate($token);
    }
}
