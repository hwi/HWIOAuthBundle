<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Tests\Fixtures;

use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\AbstractOAuthToken;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;

final class CustomOAuthToken extends OAuthToken
{
    public static function createLoggedIn(array $accessToken = []): self
    {
        $token = new self(
            $accessToken + [
                'access_token' => 'access_token_data',
            ],
            [
                'ROLE_USER',
            ]
        );

        $token->setResourceOwnerName('fake');
        $token->setUser(new User());

        return $token;
    }

    public function copyPersistentDataFrom(AbstractOAuthToken $token): void
    {
        if ($token instanceof self) {
            if ($token->hasAttribute('persistent_key')) {
                $this->setAttribute('persistent_key', $token->getAttribute('persistent_key'));
            }
        }

        parent::copyPersistentDataFrom($token);
    }
}
