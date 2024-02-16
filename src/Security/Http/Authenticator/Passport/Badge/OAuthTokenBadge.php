<?php

declare(strict_types=1);

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Security\Http\Authenticator\Passport\Badge;

use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\BadgeInterface;

final class OAuthTokenBadge implements BadgeInterface
{
    private OAuthToken $token;

    public function __construct(OAuthToken $token)
    {
        $this->token = $token;
    }

    public function isResolved(): bool
    {
        return true;
    }

    public function getToken(): OAuthToken
    {
        return $this->token;
    }
}
