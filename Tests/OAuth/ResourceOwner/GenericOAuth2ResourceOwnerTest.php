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

use HWI\Bundle\OAuthBundle\OAuth\Exception\HttpTransportException;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\GenericOAuth2ResourceOwner;
use HWI\Bundle\OAuthBundle\OAuth\Response\AbstractUserResponse;
use HWI\Bundle\OAuthBundle\OAuth\State\State;
use HWI\Bundle\OAuthBundle\OAuth\StateInterface;
use HWI\Bundle\OAuthBundle\Security\Helper\NonceGenerator;
use HWI\Bundle\OAuthBundle\Tests\Fixtures\CustomUserResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\Exception\ExceptionInterface;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class GenericOAuth2ResourceOwnerTest extends ResourceOwnerTestCase
{
    protected string $resourceOwnerClass = GenericOAuth2ResourceOwner::class;
    protected $tokenData = ['access_token' => 'token'];

    protected $options = [
        'client_id' => 'clientid',
        'client_secret' => 'clientsecret',

        'infos_url' => 'http://user.info/?test=1',
        'authorization_url' => 'http://user.auth/?test=2',
        'access_token_url' => 'http://user.access/?test=3',

        'attr_name' => 'access_token',
    ];

    protected $userResponse = <<<json
{
    "id":  "1",
    "foo": "bar"
}
json;

    protected $paths = [
        'identifier' => 'id',
        'nickname' => 'foo',
        'realname' => 'foo_disp',
    ];

    protected $authorizationUrlBasePart = 'http://user.auth/?test=2&response_type=code&client_id=clientid';
    protected $redirectUrlPart = '&redirect_uri=http%3A%2F%2Fredirect.to%2F';
    protected $authorizationUrlParams = [];

    public function testUndefinedOptionThrowsException()
    {
        $this->expectException(ExceptionInterface::class);

        $this->createResourceOwner(['non_existing' => null]);
    }

    public function testInvalidOptionValueThrowsException()
    {
        $this->expectException(ExceptionInterface::class);

        $this->createResourceOwner(['csrf' => 'invalid']);
    }

    public function testHandleRequest()
    {
        $resourceOwner = $this->createResourceOwner();

        $request = new Request(['test' => 'test']);

        $this->assertFalse($resourceOwner->handles($request));

        $request = new Request(['code' => 'test']);

        $this->assertTrue($resourceOwner->handles($request));

        $request = new Request(['code' => 'test', 'test' => 'test']);

        $this->assertTrue($resourceOwner->handles($request));
    }

    public function testGetUserInformation()
    {
        $resourceOwner = $this->createResourceOwner(
            [],
            [],
            [
                $this->createMockResponse($this->userResponse),
            ]
        );

        /** @var AbstractUserResponse $userResponse */
        $userResponse = $resourceOwner->getUserInformation($this->tokenData);

        $this->assertEquals('1', $userResponse->getUsername());
        $this->assertEquals('bar', $userResponse->getNickname());
        $this->assertEquals('token', $userResponse->getAccessToken());
        $this->assertNull($userResponse->getRefreshToken());
        $this->assertNull($userResponse->getExpiresIn());
    }

    public function testGetUserInformationFailure()
    {
        $this->expectException(HttpTransportException::class);

        $resourceOwner = $this->createResourceOwner(
            [],
            [],
            [
                $this->createMockResponse('invalid', null, 401),
            ]
        );
        $resourceOwner->getUserInformation($this->tokenData);
    }

    public function testGetAuthorizationUrl()
    {
        if (!$this->csrf) {
            $state = new State(null);
        } else {
            $state = new State(['csrf_token' => NonceGenerator::generate()]);
        }

        $resourceOwner = $this->createResourceOwner([], [], [], $state);

        if (!$this->csrf) {
            $this->storage->expects($this->never())
                ->method('save');

            $expectedUrl = $this->authorizationUrlBasePart.$this->redirectUrlPart;
        } else {
            $this->storage->expects($this->once())
                ->method('save')
                ->with($resourceOwner, $state->getCsrfToken(), 'csrf_state');

            $expectedUrl = $this->getExpectedAuthorizationUrlWithState($state->encode());
        }

        $this->assertEquals(
            $expectedUrl,
            $resourceOwner->getAuthorizationUrl('http://redirect.to/', $this->authorizationUrlParams)
        );
    }

    public function testGetState()
    {
        $stateParams = ['initial_state_param_1' => 'value'];
        if (!$this->csrf) {
            $initialState = new State($stateParams);
        } else {
            $initialState = new State(array_merge($stateParams, ['csrf_token' => NonceGenerator::generate()]));
        }

        $resourceOwner = $this->createResourceOwner([], [], [], $initialState);
        $this->storage->expects($this->once())
            ->method('fetch')
            ->with($resourceOwner, State::class, 'state')
            ->willReturn(serialize(new State(['state' => 'some-state'])));

        $state = $resourceOwner->getState();
        self::assertEquals($state->get('initial_state_param_1'), 'value');
        self::assertEquals($state->get('state'), 'some-state');
    }

    public function testGetStateWithoutStoredValues()
    {
        $resourceOwner = $this->createResourceOwner([], [], [], new State(null));
        $this->storage->expects($this->once())
            ->method('fetch')
            ->with($resourceOwner, State::class, 'state')
            ->willThrowException(new \InvalidArgumentException('No data available in storage.'));

        $state = $resourceOwner->getState();
        self::assertEmpty($state->getAll());
    }

    public function testGetAuthorizationUrlWithEnabledCsrf()
    {
        if ($this->csrf) {
            $this->markTestSkipped('CSRF is enabled for this Resource Owner.');
        }

        $nonce = NonceGenerator::generate();
        $state = new State(['csrf_token' => $nonce]);
        $resourceOwner = $this->createResourceOwner(['csrf' => true], [], [], $state);

        $this->storage->expects($this->once())
            ->method('save')
            ->with($resourceOwner, $nonce, 'csrf_state');

        $this->assertEquals(
            $this->getExpectedAuthorizationUrlWithState($state->encode()),
            $resourceOwner->getAuthorizationUrl('http://redirect.to/', $this->authorizationUrlParams)
        );

        $this->state = $state->encode();
    }

    /**
     * @dataProvider provideAccessTokenData
     */
    public function testGetAccessToken(string $response, string $contentType)
    {
        $resourceOwner = $this->createResourceOwner(
            [],
            [],
            [
                $this->createMockResponse($response, $contentType),
            ]
        );

        $request = new Request(['code' => 'somecode']);

        $this->assertEquals(
            ['access_token' => 'code'],
            $resourceOwner->getAccessToken($request, 'http://redirect.to/')
        );
    }

    public function provideAccessTokenData(): iterable
    {
        yield 'plain text' => [
            'access_token=code',
            'text/plain',
        ];

        yield 'json' => [
            '{"access_token": "code"}',
            'application/json',
        ];

        yield 'json with charset' => [
            '{"access_token": "code"}',
            'application/json; charset=utf-8',
        ];

        yield 'javascript' => [
            '{"access_token": "code"}',
            'text/javascript',
        ];

        yield 'javascript with charset' => [
            '{"access_token": "code"}',
            'text/javascript; charset=utf-8',
        ];
    }

    public function testGetAccessTokenFailedResponse()
    {
        $this->expectException(AuthenticationException::class);

        $request = new Request(['code' => 'code']);

        $resourceOwner = $this->createResourceOwner(
            [],
            [],
            [
                $this->createMockResponse('invalid'),
            ]
        );
        $resourceOwner->getAccessToken($request, 'http://redirect.to/');
    }

    public function testGetAccessTokenErrorResponse()
    {
        $this->expectException(AuthenticationException::class);

        $request = new Request(['code' => 'code']);

        $resourceOwner = $this->createResourceOwner(
            [],
            [],
            [
                $this->createMockResponse('error=foo'),
            ]
        );
        $resourceOwner->getAccessToken($request, 'http://redirect.to/');
    }

    /**
     * @dataProvider provideRefreshToken
     */
    public function testRefreshAccessToken($response, $contentType)
    {
        $resourceOwner = $this->createResourceOwner(
            [],
            [],
            [
                $this->createMockResponse($response, $contentType),
            ]
        );

        $accessToken = $resourceOwner->refreshAccessToken('foo');

        $this->assertEquals('bar', $accessToken['access_token']);
        $this->assertEquals(3600, $accessToken['expires_in']);
    }

    public function provideRefreshToken(): iterable
    {
        yield 'correct token' => [
            '{"access_token": "bar", "expires_in": 3600}',
            'application/json',
        ];
    }

    /**
     * @dataProvider provideInvalidRefreshToken
     */
    public function testRefreshAccessTokenInvalid(string $response, string $exceptionClass)
    {
        $this->expectException($exceptionClass);

        $resourceOwner = $this->createResourceOwner(
            [],
            [],
            [
                $this->createMockResponse($response),
            ]
        );
        $resourceOwner->refreshAccessToken('foo');
    }

    public function provideInvalidRefreshToken(): iterable
    {
        yield 'invalid' => [
            'invalid',
            AuthenticationException::class,
        ];

        yield 'invalid json' => [
            '{"error": "invalid"}',
            AuthenticationException::class,
        ];
    }

    public function testRevokeToken()
    {
        $this->expectException(AuthenticationException::class);

        $resourceOwner = $this->createResourceOwner();
        $resourceOwner->revokeToken('token');
    }

    public function testGetSetName()
    {
        $resourceOwner = $this->createResourceOwner();
        $this->assertEquals($this->prepareResourceOwnerName(), $resourceOwner->getName());
    }

    public function testCsrfTokenIsValidWhenDisabled()
    {
        if ($this->csrf) {
            $this->markTestSkipped('CSRF is enabled for this Resource Owner.');
        }

        $resourceOwner = $this->createResourceOwner();

        $this->storage->expects($this->never())
            ->method('fetch');

        $this->assertTrue($resourceOwner->isCsrfTokenValid('whatever you want'));
    }

    public function testCsrfTokenValid()
    {
        $resourceOwner = $this->createResourceOwner(['csrf' => true]);

        $this->storage->expects($this->once())
            ->method('fetch')
            ->with($resourceOwner, 'valid_token', 'csrf_state')
            ->willReturn('valid_token');

        $this->assertTrue($resourceOwner->isCsrfTokenValid('valid_token'));
    }

    public function testCsrfTokenInvalid()
    {
        $this->expectException(AuthenticationException::class);

        $resourceOwner = $this->createResourceOwner(['csrf' => true]);

        $this->storage->expects($this->once())
            ->method('fetch')
            ->with($resourceOwner, 'invalid_token', 'csrf_state')
            ->will($this->throwException(new InvalidOptionsException('No data available in storage.')));

        $resourceOwner->isCsrfTokenValid('invalid_token');
    }

    public function testCsrfTokenMissing()
    {
        $this->expectException(AuthenticationException::class);

        $resourceOwner = $this->createResourceOwner(['csrf' => true]);

        $resourceOwner->isCsrfTokenValid(null);
    }

    public function testCustomResponseClass()
    {
        $class = CustomUserResponse::class;

        $resourceOwner = $this->createResourceOwner(
            ['user_response_class' => $class],
            [],
            [
                $this->createMockResponse($this->userResponse),
            ]
        );

        $userResponse = $resourceOwner->getUserInformation($this->tokenData);

        $this->assertInstanceOf($class, $userResponse);
        $this->assertEquals('foo666', $userResponse->getUsername());
        $this->assertEquals('foo', $userResponse->getNickname());
        $this->assertEquals('token', $userResponse->getAccessToken());
        $this->assertNull($userResponse->getRefreshToken());
        $this->assertNull($userResponse->getExpiresIn());
    }

    protected function createResourceOwner(
        array $options = [],
        array $paths = [],
        array $responses = [],
        ?StateInterface $state = null
    ): GenericOAuth2ResourceOwner {
        /** @var GenericOAuth2ResourceOwner $resourceOwner */
        $resourceOwner = parent::createResourceOwner($options, $paths, $responses);

        $reflection = new \ReflectionClass(\get_class($resourceOwner));
        $stateProperty = $reflection->getProperty('state');
        $stateProperty->setAccessible(true);
        $stateProperty->setValue($resourceOwner, $state ?: new State($this->state));

        return $resourceOwner;
    }

    private function getExpectedAuthorizationUrlWithState($stateParameter): string
    {
        // urlencode state parameter since AbstractResourceOwner::normalizeUrl() http_build_query method encodes them again
        return $this->authorizationUrlBasePart.'&state='.urlencode($stateParameter).$this->redirectUrlPart;
    }
}
