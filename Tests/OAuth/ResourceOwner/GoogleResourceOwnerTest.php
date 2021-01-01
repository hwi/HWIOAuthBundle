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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\GoogleResourceOwner;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface;
use Symfony\Component\OptionsResolver\Exception\ExceptionInterface;
use Symfony\Component\Security\Http\HttpUtils;

class GoogleResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected string $resourceOwnerClass = GoogleResourceOwner::class;
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
        'firstname' => 'given_name',
        'lastname' => 'family_name',
        'email' => 'email',
        'profilepicture' => 'picture',
    ];

    protected $authorizationUrlBasePart = 'http://user.auth/?test=2&response_type=code&client_id=clientid&scope=https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fuserinfo.profile';
    protected $redirectUrlPart = '&redirect_uri=http%3A%2F%2Fredirect.to%2F&access_type=offline';

    public function testInvalidAccessTypeOptionValueThrowsException()
    {
        $this->expectException(ExceptionInterface::class);

        $this->createResourceOwner(['access_type' => 'invalid']);
    }

    public function testInvalidApprovalPromptOptionValueThrowsException()
    {
        $this->expectException(ExceptionInterface::class);

        $this->createResourceOwner(['approval_prompt' => 'invalid']);
    }

    public function testGetAuthorizationUrl()
    {
        $resourceOwner = $this->createResourceOwner();

        $this->assertEquals(
            $this->options['authorization_url'].'&response_type=code&client_id=clientid&scope=https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fuserinfo.profile&state=eyJzdGF0ZSI6InJhbmRvbSJ9&redirect_uri=http%3A%2F%2Fredirect.to%2F&access_type=offline',
            $resourceOwner->getAuthorizationUrl('http://redirect.to/')
        );
    }

    public function testRequestVisibleActions()
    {
        $resourceOwner = $this->createResourceOwner(
            ['request_visible_actions' => 'http://schemas.google.com/AddActivity']
        );

        $this->assertEquals(
            $this->options['authorization_url'].'&response_type=code&client_id=clientid&scope=https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fuserinfo.profile&state=eyJzdGF0ZSI6InJhbmRvbSJ9&redirect_uri=http%3A%2F%2Fredirect.to%2F&access_type=offline&request_visible_actions=http%3A%2F%2Fschemas.google.com%2FAddActivity',
            $resourceOwner->getAuthorizationUrl('http://redirect.to/')
        );
    }

    public function testApprovalPromptForce()
    {
        $resourceOwner = $this->createResourceOwner(['approval_prompt' => 'force']);

        $this->assertEquals(
            $this->options['authorization_url'].'&response_type=code&client_id=clientid&scope=https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fuserinfo.profile&state=eyJzdGF0ZSI6InJhbmRvbSJ9&redirect_uri=http%3A%2F%2Fredirect.to%2F&access_type=offline&approval_prompt=force',
            $resourceOwner->getAuthorizationUrl('http://redirect.to/')
        );
    }

    public function testHdParameter()
    {
        $resourceOwner = $this->createResourceOwner(['hd' => 'mycollege.edu']);
        $this->assertEquals(
            $this->options['authorization_url'].'&response_type=code&client_id=clientid&scope=https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fuserinfo.profile&state=eyJzdGF0ZSI6InJhbmRvbSJ9&redirect_uri=http%3A%2F%2Fredirect.to%2F&access_type=offline&hd=mycollege.edu',
            $resourceOwner->getAuthorizationUrl('http://redirect.to/')
        );

        $resourceOwner = $this->createResourceOwner(['hd' => 'mycollege.edu, mycollege.org']);
        $this->assertEquals(
            $this->options['authorization_url'].'&response_type=code&client_id=clientid&scope=https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fuserinfo.profile&state=eyJzdGF0ZSI6InJhbmRvbSJ9&redirect_uri=http%3A%2F%2Fredirect.to%2F&access_type=offline&hd=mycollege.edu&mycollege.org',
            $resourceOwner->getAuthorizationUrl('http://redirect.to/')
        );
    }

    public function testRevokeToken()
    {
        $resourceOwner = $this->createResourceOwner(
            [],
            [],
            [
                $this->createMockResponse('{"access_token": "bar"}', 'application/json'),
            ]
        );

        $this->assertTrue($resourceOwner->revokeToken('token'));
    }

    public function testRevokeTokenFails()
    {
        $resourceOwner = $this->createResourceOwner(
            [],
            [],
            [
                $this->createMockResponse('{"access_token": "bar"}', 'application/json', 401),
            ]
        );

        $this->assertFalse($resourceOwner->revokeToken('token'));
    }

    protected function setUpResourceOwner(string $name, HttpUtils $httpUtils, array $options, array $responses): ResourceOwnerInterface
    {
        return parent::setUpResourceOwner(
            $name,
            $httpUtils,
            array_merge(
                [
                    'access_type' => 'offline',
                ],
                $options
            ),
            $responses
        );
    }
}
