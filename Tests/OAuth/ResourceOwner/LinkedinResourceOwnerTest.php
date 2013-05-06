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

    public function testGetUserInformation()
    {
        $this->mockBuzz($this->userResponse, 'application/json; charset=utf-8');

        $accessToken  = array('oauth_token' => 'token', 'oauth_token_secret' => 'secret', 'oauth_expires_in' => '5183997', 'oauth_authorization_expires_in' => '5183997');
        $userResponse = $this->resourceOwner->getUserInformation($accessToken);

        $this->assertEquals('1', $userResponse->getUsername());
        $this->assertEquals('bar', $userResponse->getNickname());
        $this->assertEquals($accessToken, $userResponse->getAccessToken());
        $this->assertEquals($accessToken['oauth_token'], $userResponse->getOAuthToken());
    }

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
