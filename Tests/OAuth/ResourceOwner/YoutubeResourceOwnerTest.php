<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Tests\OAuth\ResourceOwner;

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\YoutubeResourceOwner;
use Symfony\Component\Security\Http\HttpUtils;

class YoutubeResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected $resourceOwnerClass = YoutubeResourceOwner::class;
    protected $userResponse = <<<json
{
    "id": "1",
    "name": "bar"
}
json;

    protected $paths = [
        'identifier' => 'id',
        'nickname' => 'name',
        'realname' => 'name',
        'email' => 'email',
        'profilepicture' => 'picture',
    ];

    protected $authorizationUrlBasePart = 'http://user.auth/?test=2&response_type=code&client_id=clientid&scope=https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fyoutube.readonly';
    protected $redirectUrlPart = '&redirect_uri=http%3A%2F%2Fredirect.to%2F&access_type=offline';

    public function testInvalidAccessTypeOptionValueThrowsException()
    {
        $this->expectException(\Symfony\Component\OptionsResolver\Exception\InvalidOptionsException::class);

        $this->createResourceOwner($this->resourceOwnerName, ['access_type' => 'invalid']);
    }

    public function testInvalidApprovalPromptOptionValueThrowsException()
    {
        $this->expectException(\Symfony\Component\OptionsResolver\Exception\InvalidOptionsException::class);

        $this->createResourceOwner($this->resourceOwnerName, ['approval_prompt' => 'invalid']);
    }

    public function testGetAuthorizationUrl()
    {
        $this->assertEquals(
            $this->options['authorization_url'].'&response_type=code&client_id=clientid&scope=https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fyoutube.readonly&state=eyJzdGF0ZSI6InJhbmRvbSJ9&redirect_uri=http%3A%2F%2Fredirect.to%2F&access_type=offline',
            $this->resourceOwner->getAuthorizationUrl('http://redirect.to/')
        );
    }

    public function testRequestVisibleActions()
    {
        $resourceOwner = $this->createResourceOwner($this->resourceOwnerName, ['request_visible_actions' => 'http://schemas.google.com/AddActivity']
        );

        $this->assertEquals(
            $this->options['authorization_url'].'&response_type=code&client_id=clientid&scope=https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fyoutube.readonly&state=eyJzdGF0ZSI6InJhbmRvbSJ9&redirect_uri=http%3A%2F%2Fredirect.to%2F&access_type=offline&request_visible_actions=http%3A%2F%2Fschemas.google.com%2FAddActivity',
            $resourceOwner->getAuthorizationUrl('http://redirect.to/')
        );
    }

    public function testApprovalPromptForce()
    {
        $resourceOwner = $this->createResourceOwner($this->resourceOwnerName, ['approval_prompt' => 'force']);

        $this->assertEquals(
            $this->options['authorization_url'].'&response_type=code&client_id=clientid&scope=https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fyoutube.readonly&state=eyJzdGF0ZSI6InJhbmRvbSJ9&redirect_uri=http%3A%2F%2Fredirect.to%2F&access_type=offline&approval_prompt=force',
            $resourceOwner->getAuthorizationUrl('http://redirect.to/')
        );
    }

    public function testHdParameter()
    {
        $resourceOwner = $this->createResourceOwner($this->resourceOwnerName, ['hd' => 'mycollege.edu']);
        $this->assertEquals(
            $this->options['authorization_url'].'&response_type=code&client_id=clientid&scope=https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fyoutube.readonly&state=eyJzdGF0ZSI6InJhbmRvbSJ9&redirect_uri=http%3A%2F%2Fredirect.to%2F&access_type=offline&hd=mycollege.edu',
            $resourceOwner->getAuthorizationUrl('http://redirect.to/')
        );
    }

    public function testRevokeToken()
    {
        $this->httpResponseHttpCode = 200;
        $this->mockHttpClient('{"access_token": "bar"}', 'application/json');

        $this->assertTrue($this->resourceOwner->revokeToken('token'));
    }

    public function testRevokeTokenFails()
    {
        $this->httpResponseHttpCode = 401;
        $this->mockHttpClient('{"access_token": "bar"}', 'application/json');

        $this->assertFalse($this->resourceOwner->revokeToken('token'));
    }

    protected function setUpResourceOwner($name, HttpUtils $httpUtils, array $options)
    {
        return parent::setUpResourceOwner(
            $name,
            $httpUtils,
            array_merge(
                [
                    'access_type' => 'offline',
                ],
                $options
            )
        );
    }
}
