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
use HWI\Bundle\OAuthBundle\Security\Http\Authenticator\OAuthAuthenticator;
use RuntimeException;
use Symfony\Component\Security\Http\Authenticator\AuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\Debug\TraceableAuthenticator;

class RefreshAccessTokenListener extends AbstractRefreshAccessTokenListener
{
    private AuthenticatorInterface $authenticator;

    public function __construct(
        AuthenticatorInterface $authenticator,
    ) {
        $this->authenticator = $authenticator;
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
        if ($this->authenticator instanceof OAuthAuthenticator) {
            return $this->authenticator->refreshToken($token);
        }

        if ($this->authenticator instanceof TraceableAuthenticator && method_exists($this->authenticator, 'getAuthenticator')) {
            $authenticator = $this->authenticator->getAuthenticator();

            if ($authenticator instanceof OAuthAuthenticator) {
                return $authenticator->refreshToken($token);
            }
        }

        throw new RuntimeException('Unsupported authenticator, expecting OAuthAuthenticator, got '.\get_class($this->authenticator));
    }
}
