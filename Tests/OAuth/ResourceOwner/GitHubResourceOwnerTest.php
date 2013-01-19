<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Tests\OAuth\ResourceOwner;

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\GitHubResourceOwner;

class GitHubResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected $userResponse = <<<json
{
    "id": "1",
    "login": "bar"
}
json;

    protected $paths = array(
        'identifier'     => 'id',
        'nickname'       => 'login',
        'realname'       => 'name',
        'email'          => 'email',
        'profilepicture' => 'avatar_url',
    );

    protected function setUpResourceOwner($name, $httpUtils, array $options)
    {
        $options = array_merge(
            array(
                 'authorization_url'   => 'https://github.com/login/oauth/authorize',
                 'access_token_url'    => 'https://github.com/login/oauth/access_token',
                 'infos_url'           => 'https://api.github.com/user',
            ),
            $options
        );

        return new GitHubResourceOwner($this->buzzClient, $httpUtils, $options, $name);
    }
}
