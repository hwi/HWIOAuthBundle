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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\LinkedinResourceOwner;

class LinkedinResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected $userResponse = <<<json
{
    "id": "1",
    "formattedName": "bar"
}
json;
    protected $paths        = array(
        'identifier'     => 'id',
        'nickname'       => 'formattedName',
        'realname'       => 'formattedName',
        'email'          => 'emailAddress',
        'profilepicture' => 'pictureUrl',
    );

    protected function setUpResourceOwner($name, $httpUtils, array $options)
    {
        $options = array_merge(
            array(
                'authorization_url'   => 'https://www.linkedin.com/uas/oauth2/authorization',
                'access_token_url'    => 'https://www.linkedin.com/uas/oauth2/accessToken',
                'infos_url'           => 'https://api.linkedin.com/v1/people/~:(id,formatted-name,email-address,picture-url)?format=json',
                'csrf'                => true,
            ),
            $options
        );

        return new LinkedinResourceOwner($this->buzzClient, $httpUtils, $options, $name, $this->storage);
    }
}
