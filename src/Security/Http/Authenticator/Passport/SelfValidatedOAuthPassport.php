<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Security\Http\Authenticator\Passport;

use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\BadgeInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

/**
 * SelfValidatingPassport contained OAuthToken.
 */
final class SelfValidatedOAuthPassport extends SelfValidatingPassport
{
    private OAuthToken $token;

    /**
     * Token already contains authenticated user. No need to create trivial UserBadge outside.
     *
     * @param BadgeInterface[] $badges
     */
    public function __construct(OAuthToken $token, array $badges = [])
    {
        $this->token = $token;

        $user = $token->getUser();

        $userBadge = class_exists(UserBadge::class)
            ? new UserBadge(
                method_exists($user, 'getUserIdentifier') ? $user->getUserIdentifier() : $user->getUsername(),
                static function () use ($user) { return $user; }
            )
            : $user;

        parent::__construct($userBadge, $badges);
    }

    public function getToken(): OAuthToken
    {
        return $this->token;
    }
}
