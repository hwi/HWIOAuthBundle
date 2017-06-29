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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\FacebookResourceOwner;
use Symfony\Component\HttpFoundation\Request;

class FacebookResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected $resourceOwnerClass = FacebookResourceOwner::class;
    protected $userResponse = <<<json
{
    "id": "1",
    "username": "bar"
}
json;

    protected $paths = array(
        'identifier' => 'id',
        'nickname' => 'username',
        'firstname' => 'first_name',
        'lastname' => 'last_name',
        'realname' => 'name',
    );

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AuthenticationException
     */
    public function testGetAccessTokenFailedResponse()
    {
        $this->mockHttpClient('{"error": {"message": "invalid"}}', 'application/json; charset=utf-8');
        $request = new Request(array('code' => 'code'));

        $this->resourceOwner->getAccessToken($request, 'http://redirect.to/');
    }

    public function testAuthTypeRerequest()
    {
        $resourceOwner = $this->createResourceOwner($this->resourceOwnerName, array('auth_type' => 'rerequest'));

        $this->assertEquals(
            $this->options['authorization_url'].'&response_type=code&client_id=clientid&state=random&redirect_uri=http%3A%2F%2Fredirect.to%2F&auth_type=rerequest',
            $resourceOwner->getAuthorizationUrl('http://redirect.to/')
        );
    }

    public function testAuthTypeRerequestAndDisplayPopup()
    {
        $resourceOwner = $this->createResourceOwner($this->resourceOwnerName, array('display' => 'popup', 'auth_type' => 'rerequest'));

        $this->assertEquals(
            $this->options['authorization_url'].'&response_type=code&client_id=clientid&state=random&redirect_uri=http%3A%2F%2Fredirect.to%2F&display=popup&auth_type=rerequest',
            $resourceOwner->getAuthorizationUrl('http://redirect.to/')
        );
    }

    public function testDisplayPopup()
    {
        $resourceOwner = $this->createResourceOwner($this->resourceOwnerName, array('display' => 'popup'));

        $this->assertEquals(
            $this->options['authorization_url'].'&response_type=code&client_id=clientid&state=random&redirect_uri=http%3A%2F%2Fredirect.to%2F&display=popup',
            $resourceOwner->getAuthorizationUrl('http://redirect.to/')
        );
    }

    /**
     * @expectedException \Symfony\Component\OptionsResolver\Exception\ExceptionInterface
     */
    public function testInvalidDisplayOptionValueThrowsException()
    {
        $this->createResourceOwner($this->resourceOwnerName, array('display' => 'invalid'));
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

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AuthenticationException
     */
    public function testGetAccessTokenErrorResponse()
    {
        $this->mockHttpClient();

        $request = new Request(array(
            'error_code' => 901,
            'error_message' => 'This app is in sandbox mode.  Edit the app configuration at http://developers.facebook.com/apps to make the app publicly visible.',
        ));

        $this->resourceOwner->getAccessToken($request, 'http://redirect.to/');
    }
}
