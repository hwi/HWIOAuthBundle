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

class LinkedinResourceOwnerTest extends GenericOAuth1ResourceOwnerTest
{
    protected $userResponse = '{"id": "1", "formattedName": "bar"}';
    protected $paths        = array(
        'identifier' => 'id',
        'nickname'   => 'formattedName',
        'realname'   => 'formattedName',
    );

    protected function setUpResourceOwner($name, $httpUtils, array $options)
    {
        $options = array_merge(
            array(
                'authorization_url'   => 'https://www.linkedin.com/uas/oauth/authenticate',
                'request_token_url'   => 'https://api.linkedin.com/uas/oauth/requestToken',
                'access_token_url'    => 'https://api.linkedin.com/uas/oauth/accessToken',
                'infos_url'           => 'http://api.linkedin.com/v1/people/~:(id,formatted-name)',
                'realm'               => 'http://api.linkedin.com'
            ),
            $options
        );

        return new LinkedinResourceOwner($this->buzzClient, $httpUtils, $options, $name, $this->storage);
    }
}
