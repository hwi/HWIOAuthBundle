<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Tests\Security;

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface;
use HWI\Bundle\OAuthBundle\OAuth\State\State;
use HWI\Bundle\OAuthBundle\Security\Http\ResourceOwnerMap;
use HWI\Bundle\OAuthBundle\Security\OAuthUtils;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Bundle\SecurityBundle\Security\FirewallConfig;
use Symfony\Bundle\SecurityBundle\Security\FirewallMap;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Http\HttpUtils;

final class OAuthUtilsTest extends TestCase
{
    private string $grantRule = 'IS_AUTHENTICATED_REMEMBERED';

    public function testGetAuthorizationUrlWithRedirectUrl(): void
    {
        $url = 'http://localhost:8080/login/check-instagram';
        $request = $this->getRequest($url);
        $redirect = 'https://api.instagram.com/oauth/authorize?redirect='.rawurlencode($url);

        $authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);

        $utils = new OAuthUtils($this->getHttpUtils($url), $authorizationChecker, $this->createFirewallMapMock(), true, $this->grantRule);
        $utils->addResourceOwnerMap('main', $this->getMap($url, $redirect, false, true));

        $this->assertEquals(
            $redirect,
            $utils->getAuthorizationUrl($request, 'instagram', $url)
        );

        $this->assertNull($request->attributes->get('service'));
    }

    public function testGetAuthorizationUrlWithConnectAndUserToken(): void
    {
        $url = 'http://localhost:8080/login/check-instagram';
        $request = $this->getRequest($url);
        $redirect = 'https://api.instagram.com/oauth/authorize?redirect='.rawurlencode($url);

        $utils = new OAuthUtils($this->getHttpUtils($url), $this->getAuthorizationChecker(true, $this->grantRule), $this->createFirewallMapMock(), true, $this->grantRule);
        $utils->addResourceOwnerMap('main', $this->getMap($url, $redirect, true, false));

        $this->assertEquals(
            $redirect,
            $utils->getAuthorizationUrl($request, 'instagram')
        );

        $this->assertEquals(
            'instagram',
            $request->attributes->get('service')
        );
    }

    #[DataProvider('provideAuthorizationUrlsWithState')]
    public function testGetAuthorizationUrlWithStateQueryParameters(string $url, string $urlWithState, string $redirect): void
    {
        $request = $this->getRequest($urlWithState);
        $resource = $this->getMockBuilder(ResourceOwnerInterface::class)->getMock();

        $utils = new OAuthUtils($this->getHttpUtils($url), $this->getAuthorizationChecker(false, $this->grantRule), $this->createFirewallMapMock(), true, $this->grantRule);
        $utils->addResourceOwnerMap('main', $this->getMap($url, $redirect, false, false, $resource));

        $captured = [];
        $resource->expects($this->exactly(2))
            ->method('addStateParameter')
            ->willReturnCallback(static function ($key, $value) use (&$captured) {
                $captured[] = [$key, $value];
            });

        $this->assertEquals(
            $redirect,
            $utils->getAuthorizationUrl($request, 'instagram')
        );

        $this->assertSame(
            [['foo', 'bar'], ['foobar', 'foobaz']],
            $captured
        );
    }

    public function testGetAuthorizationUrlWithoutUserToken(): void
    {
        $url = 'http://localhost:8080/login/check-instagram';
        $request = $this->getRequest($url);
        $redirect = 'https://api.instagram.com/oauth/authorize?redirect='.rawurlencode($url);

        $utils = new OAuthUtils($this->getHttpUtils($url), $this->getAuthorizationChecker(false, $this->grantRule), $this->createFirewallMapMock(), true, $this->grantRule);
        $utils->addResourceOwnerMap('main', $this->getMap($url, $redirect));

        $this->assertEquals(
            $redirect,
            $utils->getAuthorizationUrl($request, 'instagram')
        );

        $this->assertNull($request->attributes->get('service'));
    }

    public function testGetAuthorizationUrlWithAuthenticatedFullyRule(): void
    {
        $url = 'http://localhost:8080/login/check-instagram';
        $request = $this->getRequest($url);
        $redirect = 'https://api.instagram.com/oauth/authorize?redirect='.rawurlencode($url);

        $utils = new OAuthUtils(
            $this->getHttpUtils($url),
            $this->getAuthorizationChecker(false, 'IS_AUTHENTICATED_FULLY'),
            $this->createFirewallMapMock(),
            true,
            'IS_AUTHENTICATED_FULLY'
        );
        $utils->addResourceOwnerMap('main', $this->getMap($url, $redirect));

        $this->assertEquals(
            $redirect,
            $utils->getAuthorizationUrl($request, 'instagram')
        );

        $this->assertNull($request->attributes->get('service'));
    }

    #[DataProvider('provideServiceAuthUrlsWithState')]
    public function testGetServiceAuthUrlWithStateQueryParameters(string $url, string $expectedResult): void
    {
        $request = $this->getRequest($url);
        $resource = $this->getMockBuilder(ResourceOwnerInterface::class)->getMock();
        $resource
            ->expects($this->any())
            ->method('getName')
            ->willReturn('instagram');

        $serviceLocator = $this->createMock(ServiceLocator::class);
        $serviceLocator->method('get')
            ->with('instagram')
            ->willReturn($resource);

        $mapMock = new ResourceOwnerMap(
            $this->createMock(HttpUtils::class),
            ['instagram' => '/fake'],
            ['instagram' => '/fake'],
            $serviceLocator
        );

        $authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $utils = new OAuthUtils($this->getHttpUtils($url), $authorizationChecker, $this->createFirewallMapMock(), true, $this->grantRule);
        $utils->addResourceOwnerMap('main', $mapMock);

        $captured = [];
        $resource->expects($this->exactly(2))
            ->method('addStateParameter')
            ->willReturnCallback(static function ($key, $value) use (&$captured) {
                $captured[] = [$key, $value];
            });

        $this->assertEquals(
            $expectedResult,
            $utils->getServiceAuthUrl($request, $resource)
        );

        $this->assertSame(
            [['foo', 'bar'], ['foobar', 'foobaz']],
            $captured
        );
    }

    #[DataProvider('provideValidData')]
    public function testSignatureIsGeneratedCorrectly(string $signature, string $url): void
    {
        // Parameters from http://oauth.net/core/1.0a/#anchor46
        $parameters = [
            'oauth_consumer_key' => 'dpf43f3p2l4k3l03',
            'oauth_token' => 'nnch734d00sl2jdk',
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => '1191242096',
            'oauth_nonce' => 'kllo9940pd9333jh',
            'oauth_version' => '1.0',
        ];

        $this->assertEquals(
            $signature,
            OAuthUtils::signRequest('GET', $url, $parameters, 'kd94hf93k423kf44', 'pfkkdhi9sl3r4s00')
        );
    }

    #[DataProvider('provideInvalidData')]
    public function testThrowsExceptionIfRequiredParameterIsMissing(array $parameters): void
    {
        $this->expectException(RuntimeException::class);

        OAuthUtils::signRequest('GET', 'http://example.com', $parameters, 'client_secret');
    }

    public function testGetLoginUrlWithStateQueryParameters(): void
    {
        $url = 'http://localhost:8080/instagram';

        $authChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $resourceOwner = $this->createMock(ResourceOwnerInterface::class);

        $serviceLocator = $this->createMock(ServiceLocator::class);
        $serviceLocator->method('get')
            ->with('instagram')
            ->willReturn($resourceOwner);

        $mapMock = new ResourceOwnerMap(
            $this->createMock(HttpUtils::class),
            ['instagram' => '/fake'],
            ['instagram' => '/fake'],
            $serviceLocator
        );

        $utils = new OAuthUtils($this->getHttpUtils($url), $authChecker, $this->createFirewallMapMock(), true, $this->grantRule);
        $utils->addResourceOwnerMap('main', $mapMock);

        $this->assertEquals(
            $url,
            $utils->getLoginUrl($this->getRequest($url), 'instagram')
        );
        $this->assertEquals(
            $url.'?state=foo',
            $utils->getLoginUrl($this->getRequest($url.'?state=foo'), 'instagram')
        );
        $this->assertEquals(
            $url.'?state%5B0%5D=foo&state%5B1%5D=bar',
            $utils->getLoginUrl($this->getRequest($url.'?state[]=foo&state[]=bar'), 'instagram')
        );
        $this->assertEquals(
            $url.'?state%5Bfoo%5D=bar&state%5Bbar%5D=baz',
            $utils->getLoginUrl($this->getRequest($url.'?state[foo]=bar&state[bar]=baz'), 'instagram')
        );
    }

    public static function provideValidData(): iterable
    {
        yield 'simple' => ['iflJZCKxEsZ58FFDyCysxfLbuKM=', 'http://photos.example.net/photos'];
        yield 'with additional data' => ['tR3+Ty81lMeYAr/Fid0kMTYa/WM=', 'http://photos.example.net/photos?file=vacation.jpg&size=original'];
    }

    public static function provideInvalidData(): iterable
    {
        yield 'missing "oauth_consumer_key"' => [['oauth_timestamp' => '', 'oauth_nonce' => '', 'oauth_version' => '', 'oauth_signature_method' => '']];

        yield 'missing "oauth_timestamp"' => [['oauth_consumer_key' => '', 'oauth_nonce' => '', 'oauth_version' => '', 'oauth_signature_method' => '']];

        yield 'missing "oauth_nonce"' => [['oauth_consumer_key' => '', 'oauth_timestamp' => '', 'oauth_version' => '', 'oauth_signature_method' => '']];

        yield 'missing "oauth_version"' => [['oauth_consumer_key' => '', 'oauth_timestamp' => '', 'oauth_nonce' => '', 'oauth_signature_method' => '']];

        yield 'missing "oauth_signature_method"' => [['oauth_consumer_key' => '', 'oauth_timestamp' => '', 'oauth_nonce' => '', 'oauth_version' => '']];
    }

    public static function provideServiceAuthUrlsWithState(): iterable
    {
        $parameters = ['foo' => 'bar', 'foobar' => 'foobaz'];
        $state = new State($parameters);

        $url = 'http://localhost:8080/service/instagram';

        yield 'state as an encoded string' => [$url.'?state='.$state->encode(), $url];

        $stateAsArray = [];
        foreach ($parameters as $key => $value) {
            $stateAsArray[] = \sprintf('state[%s]=%s', $key, rawurlencode($value));
        }

        yield 'state as an array' => [$url.'?'.implode('&', $stateAsArray), $url];
    }

    public static function provideAuthorizationUrlsWithState(): iterable
    {
        $parameters = ['foo' => 'bar', 'foobar' => 'foobaz'];
        $state = new State($parameters);

        $url = 'http://localhost:8080/login/check-instagram';
        $redirect = 'https://api.instagram.com/oauth/authorize?redirect='.rawurlencode($url);

        yield 'state as an encoded string' => [$url, $url.'?state='.$state->encode(), $redirect];

        $stateAsArray = [];
        foreach ($parameters as $key => $value) {
            $stateAsArray[] = \sprintf('state[%s]=%s', $key, rawurlencode($value));
        }

        yield 'state as an array' => [$url, $url.'?'.implode('&', $stateAsArray), $redirect];
    }

    private function getRequest(string $url): Request
    {
        return Request::create($url, 'get', [], [], [], ['SERVER_PORT' => 8080]);
    }

    private function getMap($url, $redirect, $hasUser = false, $hasOneRedirectUrl = false, $resource = null): ResourceOwnerMap
    {
        $resource = $resource ?? $this->createMock(ResourceOwnerInterface::class);

        $resource
            ->expects($this->once())
            ->method('getAuthorizationUrl')
            ->with($url, [])
            ->willReturn($redirect);

        $resource
            ->expects($this->any())
            ->method('getOption')
            ->with('auth_with_one_url')
            ->willReturn($hasOneRedirectUrl);

        $serviceLocator = $this->createMock(ServiceLocator::class);
        $serviceLocator->method('get')
            ->with('instagram')
            ->willReturn($resource);

        $utils = $this->createMock(HttpUtils::class);

        if (!$hasUser && !$hasOneRedirectUrl) {
            $utils->method('checkRequestPath')
                ->willReturn(true);
        }

        $ownerMap = new ResourceOwnerMap(
            $utils,
            ['instagram' => '/fake'],
            ['instagram' => !$hasUser && !$hasOneRedirectUrl ? '/login/check-instagram' : '/fake'],
            $serviceLocator
        );

        if ($hasUser) {
            $resource
                ->expects($this->once())
                ->method('getName')
                ->willReturn('instagram');
        }

        return $ownerMap;
    }

    private function getHttpUtils(string $generatedUrl = '/'): HttpUtils
    {
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);

        $urlGenerator
            ->expects($this->any())
            ->method('generate')
            ->willReturn($generatedUrl)
        ;

        return new HttpUtils($urlGenerator);
    }

    private function createFirewallMapMock(): FirewallMap
    {
        $firewallMap = $this->createMock(FirewallMap::class);

        $firewallMap
            ->expects($this->any())
            ->method('getFirewallConfig')
            ->willReturn(new FirewallConfig('main', '/path/a'))
        ;

        return $firewallMap;
    }

    private function getAuthorizationChecker($hasUser, $grantRule)
    {
        $mock = $this->createMock(AuthorizationCheckerInterface::class);
        $mock->expects($this->once())
            ->method('isGranted')
            ->with($grantRule)
            ->willReturn($hasUser);

        return $mock;
    }
}
