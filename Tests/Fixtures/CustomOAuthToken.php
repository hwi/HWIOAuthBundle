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

use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;

class CustomOAuthToken extends OAuthToken
{
    public function __construct()
    {
        parent::__construct([
            'access_token' => 'access_token_data',
        ], [
            'ROLE_USER',
        ]);

        $this->setUser(class_exists(\FOS\UserBundle\Model\User::class) ? new FOSUser() : new User());
    }
}
