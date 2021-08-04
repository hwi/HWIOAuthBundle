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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\FacebookResourceOwner;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\Exception\ExceptionInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class FacebookResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected string $resourceOwnerClass = FacebookResourceOwner::class;
    protected $userResponse = <<<json
{
    "id": "1",
    "username": "bar"
}
json;

    protected array $paths = [
        'identifier' => 'id',
        'nickname' => 'username',
        'firstname' => 'first_name',
        'lastname' => 'last_name',
        'realname' => 'name',
        'email' => 'email',
        'profilepicture' => 'picture.data.url',
    ];

    public function testGetAccessTokenFailedResponse()
    {
        $this->expectException(AuthenticationException::class);

        $resourceOwner = $this->createResourceOwner(
            [],
            [],
            [
                $this->createMockResponse('{"error": {"message": "invalid"}}', 'application/json; charset=utf-8'),
            ]
        );

        $request = new Request(['code' => 'code']);

        $resourceOwner->getAccessToken($request, 'http://redirect.to/');
    }

    public function testAuthTypeRerequest()
    {
        $resourceOwner = $this->createResourceOwner(['auth_type' => 'rerequest']);

        $this->assertEquals(
            $this->options['authorization_url'].'&response_type=code&client_id=clientid&state=eyJzdGF0ZSI6InJhbmRvbSJ9&redirect_uri=http%3A%2F%2Fredirect.to%2F&auth_type=rerequest',
            $resourceOwner->getAuthorizationUrl('http://redirect.to/')
        );
    }

    public function testAuthTypeRerequestAndDisplayPopup()
    {
        $resourceOwner = $this->createResourceOwner(['display' => 'popup', 'auth_type' => 'rerequest']);

        $this->assertEquals(
            $this->options['authorization_url'].'&response_type=code&client_id=clientid&state=eyJzdGF0ZSI6InJhbmRvbSJ9&redirect_uri=http%3A%2F%2Fredirect.to%2F&display=popup&auth_type=rerequest',
            $resourceOwner->getAuthorizationUrl('http://redirect.to/')
        );
    }

    public function testDisplayPopup()
    {
        $resourceOwner = $this->createResourceOwner(['display' => 'popup']);

        $this->assertEquals(
            $this->options['authorization_url'].'&response_type=code&client_id=clientid&state=eyJzdGF0ZSI6InJhbmRvbSJ9&redirect_uri=http%3A%2F%2Fredirect.to%2F&display=popup',
            $resourceOwner->getAuthorizationUrl('http://redirect.to/')
        );
    }

    public function testInvalidDisplayOptionValueThrowsException()
    {
        $this->expectException(ExceptionInterface::class);

        $this->createResourceOwner(['display' => 'invalid']);
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

    public function testGetAccessTokenErrorResponse()
    {
        $this->expectException(AuthenticationException::class);

        $request = new Request([
            'error_code' => 901,
            'error_message' => 'This app is in sandbox mode.  Edit the app configuration at http://developers.facebook.com/apps to make the app publicly visible.',
        ]);

        $resourceOwner = $this->createResourceOwner();
        $resourceOwner->getAccessToken($request, 'http://redirect.to/');
    }
}
