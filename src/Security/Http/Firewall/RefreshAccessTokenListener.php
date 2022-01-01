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

use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\AbstractOAuthToken;
use HWI\Bundle\OAuthBundle\Security\Http\Authenticator\OAuthAuthenticator;

class RefreshAccessTokenListener extends AbstractRefreshAccessTokenListener
{
    private OAuthAuthenticator $oAuthAuthenticator;

    public function __construct(
        OAuthAuthenticator $oAuthAuthenticator
    ) {
        $this->oAuthAuthenticator = $oAuthAuthenticator;
    }

    /**
     * @template T of AbstractOAuthToken
     *
     * @param T $token
     *
     * @return T
     */
    protected function refreshToken(AbstractOAuthToken $token): AbstractOAuthToken
    {
        return $this->oAuthAuthenticator->refreshToken($token);
    }
}
