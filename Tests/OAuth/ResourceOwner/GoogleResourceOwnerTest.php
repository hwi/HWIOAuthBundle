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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\GoogleResourceOwner;

class GoogleResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected $userResponse = <<<json
{
    "id": "1",
    "name": "bar"
}
json;

    protected $paths = array(
        'identifier'     => 'id',
        'nickname'       => 'name',
        'realname'       => 'name',
        'email'          => 'email',
        'profilepicture' => 'picture',
    );

    public function testGetOptionAccessType()
    {
        $this->assertEquals('offline', $this->resourceOwner->getOption('access_type'));
    }

    public function testGetAuthorizationUrl()
    {
        $this->assertEquals(
            $this->options['authorization_url'].'&response_type=code&client_id=clientid&state=random&redirect_uri=http%3A%2F%2Fredirect.to%2F&access_type=offline',
            $this->resourceOwner->getAuthorizationUrl('http://redirect.to/')
        );
    }

    public function testRequestVisibleActions()
    {
        $resourceOwner = $this->createResourceOwner('google', array('request_visible_actions' => 'http://schemas.google.com/AddActivity'));
        $this->assertEquals(
            $this->options['authorization_url'].'&response_type=code&client_id=clientid&state=random&redirect_uri=http%3A%2F%2Fredirect.to%2F&access_type=offline&request_visible_actions=http%3A%2F%2Fschemas.google.com%2FAddActivity',
            $resourceOwner->getAuthorizationUrl('http://redirect.to/')
        );
    }

    public function testApprovalPromptForce()
    {
        $resourceOwner = $this->createResourceOwner('google', array('approval_prompt' => 'force'));
        $this->assertEquals(
            $this->options['authorization_url'].'&response_type=code&client_id=clientid&state=random&redirect_uri=http%3A%2F%2Fredirect.to%2F&access_type=offline&approval_prompt=force',
            $resourceOwner->getAuthorizationUrl('http://redirect.to/')
        );
    }

    public function testHdParameter()
    {
        $resourceOwner = $this->createResourceOwner('google', array('hd' => 'mycollege.edu'));
        $this->assertEquals(
            $this->options['authorization_url'].'&response_type=code&client_id=clientid&state=random&redirect_uri=http%3A%2F%2Fredirect.to%2F&access_type=offline&hd=mycollege.edu',
            $resourceOwner->getAuthorizationUrl('http://redirect.to/')
        );
    }

    public function testRevokeToken()
    {
        $this->buzzResponseHttpCode = 200;
        $this->mockBuzz('{"access_token": "bar"}', 'application/json');

        $this->assertTrue($this->resourceOwner->revokeToken('token'));
    }

    public function testRevokeTokenFails()
    {
        $this->buzzResponseHttpCode = 401;
        $this->mockBuzz('{"access_token": "bar"}', 'application/json');

        $this->assertFalse($this->resourceOwner->revokeToken('token'));
    }

    protected function setUpResourceOwner($name, $httpUtils, array $options)
    {
        $options = array_merge(
            array(
                'authorization_url' => 'https://accounts.google.com/o/oauth2/auth',
                'access_token_url'  => 'https://accounts.google.com/o/oauth2/token',
                'infos_url'         => 'https://www.googleapis.com/oauth2/v1/userinfo',
                'scope'             => 'https://www.googleapis.com/auth/userinfo.profile',

                'access_type'       => 'offline'
            ),
            $options
        );

        return new GoogleResourceOwner($this->buzzClient, $httpUtils, $options, $name, $this->storage);
    }
}
