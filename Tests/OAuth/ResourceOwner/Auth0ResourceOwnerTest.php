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

use Http\Discovery\MessageFactoryDiscovery;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\Auth0ResourceOwner;
use Psr\Http\Message\RequestInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\HttpUtils;

class Auth0ResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected $resourceOwnerClass = Auth0ResourceOwner::class;
    protected $userResponse = <<<json
{
  "email": "baz",
  "email_verified": false,
  "clientID": "yyy",
  "picture": "https://secure.gravatar.com/avatar/xxx.png",
  "user_id": "1",
  "name": "bar",
  "nickname": "bar",
  "identities": [
    {
      "user_id": "xxx",
      "provider": "auth0",
      "connection": "Username-Password-Authentication",
      "isSocial": false
    }
  ]
}
json;

    protected $paths = [
        'identifier' => 'user_id',
        'nickname' => 'nickname',
        'realname' => 'name',
        'email' => 'email',
        'profilepicture' => 'picture',
    ];

    protected $authorizationUrlBasePart = 'https://example.oauth0.com/authorize?auth0Client=eyJuYW1lIjoiSFdJT0F1dGhCdW5kbGUiLCJ2ZXJzaW9uIjoidW5rbm93biIsImVudmlyb25tZW50Ijp7Im5hbWUiOiJQSFAiLCJ2ZXJzaW9uIjoiRkFLRV9QSFBfVkVSU0lPTl9GT1JfVEVTVFMifX0=&response_type=code&client_id=clientid';

    /**
     * Tests if {@see Auth0ResourceOwner::getAccessToken} would send the expected request to Auth0.
     */
    public function testGetAccessTokenSendsExpectedRequest(): void
    {
        $expectedRequestUri = 'https://example.oauth0.com/oauth/token';
        $expectedRequestMethod = 'POST';
        $expectedAuthorizationHeader = [
            'Basic Y2xpZW50aWQ6Y2xpZW50c2VjcmV0',
        ];
        $expectedAuth0ClientRequestHeader = [
            'eyJuYW1lIjoiSFdJT0F1dGhCdW5kbGUiLCJ2ZXJzaW9uIjoidW5rbm93biIsImVudmlyb25tZW50Ijp7Im5hbWUiOiJQSFAiLCJ2ZXJzaW9uIjoiRkFLRV9QSFBfVkVSU0lPTl9GT1JfVEVTVFMifX0=',
        ];
        $expectedRequestBodyContents = 'code=somecode&grant_type=authorization_code&redirect_uri=http%3A%2F%2Fredirect.to%2F';

        $this->mockHttpClientSendRequestWithRequestAssertions(
            $expectedRequestUri,
            $expectedRequestMethod,
            $expectedAuthorizationHeader,
            $expectedAuth0ClientRequestHeader,
            $expectedRequestBodyContents
        );

        $request = new Request(['code' => 'somecode']);

        $this->resourceOwner->getAccessToken($request, 'http://redirect.to/');
    }

    /**
     * Tests if {@see Auth0ResourceOwner::getUserInformation} would send the expected request to Auth0.
     */
    public function testGetUserInformationSendsExpectedRequest(): void
    {
        $expectedRequestUri = 'https://example.oauth0.com/userinfo';
        $expectedRequestMethod = 'GET';
        $expectedAuthorizationHeader = [
            'Bearer token',
        ];
        $expectedAuth0ClientRequestHeader = [
            'eyJuYW1lIjoiSFdJT0F1dGhCdW5kbGUiLCJ2ZXJzaW9uIjoidW5rbm93biIsImVudmlyb25tZW50Ijp7Im5hbWUiOiJQSFAiLCJ2ZXJzaW9uIjoiRkFLRV9QSFBfVkVSU0lPTl9GT1JfVEVTVFMifX0=',
        ];

        $this->mockHttpClientSendRequestWithRequestAssertions(
            $expectedRequestUri,
            $expectedRequestMethod,
            $expectedAuthorizationHeader,
            $expectedAuth0ClientRequestHeader,
            ''
        );

        $this->resourceOwner->getUserInformation($this->tokenData);
    }

    protected function setUpResourceOwner($name, HttpUtils $httpUtils, array $options)
    {
        $auth0Client = base64_encode(json_encode([
            'name' => 'HWIOAuthBundle',
            'version' => 'unknown',
            'environment' => [
                'name' => 'PHP',
                'version' => 'FAKE_PHP_VERSION_FOR_TESTS',
            ],
        ]));

        $options = array_merge(
            $options,
            [
                'authorization_url' => '{base_url}/authorize?auth0Client='.$auth0Client,
                'access_token_url' => '{base_url}/oauth/token',
                'infos_url' => '{base_url}/userinfo',
                'auth0_client' => $auth0Client,
                'base_url' => 'https://example.oauth0.com',
            ]
        );

        return parent::setUpResourceOwner($name, $httpUtils, $options);
    }

    /**
     * Mocks the {@see HttpClient::sendRequest} method with assertions on the request.
     *
     * @param string $expectedRequestUri
     * @param string $expectedRequestMethod
     * @param array  $expectedAuthorizationHeader
     * @param array  $expectedAuth0ClientRequestHeader
     * @param string $expectedRequestBodyContents
     */
    private function mockHttpClientSendRequestWithRequestAssertions(
        string $expectedRequestUri,
        string $expectedRequestMethod,
        array $expectedAuthorizationHeader,
        array $expectedAuth0ClientRequestHeader,
        string $expectedRequestBodyContents
    ): void {
        $this->httpClient->expects($this->once())
            ->method('sendRequest')
            ->with(
                $this->callback(function (RequestInterface $request) use ($expectedRequestUri, $expectedRequestMethod, $expectedAuthorizationHeader, $expectedAuth0ClientRequestHeader, $expectedRequestBodyContents) {
                    $this->assertEquals($expectedRequestUri, $request->getUri());
                    $this->assertSame(
                        $expectedRequestMethod,
                        $request->getMethod(),
                        'The request should be send with the expected request method.'
                    );
                    $this->assertSame(
                        $expectedAuthorizationHeader,
                        $request->getHeader('Authorization'),
                        'The Authorization header should be added to the request with the Base64 encoded client_id and client_secret or with a bearer token.'
                    );
                    $this->assertSame(
                        $expectedAuth0ClientRequestHeader,
                        $request->getHeader('Auth0-Client'),
                        'The Auth0-Client header should be added with the expected Base64 encoded version information.'
                    );
                    $this->assertSame(
                        $expectedRequestBodyContents,
                        $request->getBody()->getContents(),
                        'The request body should contain the expected content / variables.'
                    );

                    return true;
                })
            )
            ->willReturnCallback(function (RequestInterface $request) {
                return MessageFactoryDiscovery::find()
                    ->createResponse(
                        $this->httpResponseHttpCode,
                        null,
                        $request->withAddedHeader('Content-Type', 'application/json')->getHeaders(),
                        '{"access_token": "code"}'
                    );
            });
    }
}
