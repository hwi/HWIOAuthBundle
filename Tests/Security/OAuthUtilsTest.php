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
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Http\HttpUtils;

class OAuthUtilsTest extends TestCase
{
    private $grantRule = 'IS_AUTHENTICATED_REMEMBERED';

    public function testGetAuthorizationUrlWithRedirectUrl()
    {
        $url = 'http://localhost:8080/login/check-instagram';
        $request = $this->getRequest($url);
        $redirect = 'https://api.instagram.com/oauth/authorize?redirect='.rawurlencode($url);

        $authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);

        $utils = new OAuthUtils($this->getHttpUtils($url), $authorizationChecker, true, $this->grantRule);
        $utils->addResourceOwnerMap($this->getMap($url, $redirect, false, true));

        $this->assertEquals(
            $redirect,
            $utils->getAuthorizationUrl($request, 'instagram', $url)
        );

        $this->assertNull($request->attributes->get('service'));
    }

    public function testGetAuthorizationUrlWithConnectAndUserToken()
    {
        $url = 'http://localhost:8080/login/check-instagram';
        $request = $this->getRequest($url);
        $redirect = 'https://api.instagram.com/oauth/authorize?redirect='.rawurlencode($url);

        $utils = new OAuthUtils($this->getHttpUtils($url), $this->getAutorizationChecker(true, $this->grantRule), true, $this->grantRule);
        $utils->addResourceOwnerMap($this->getMap($url, $redirect, true, false));

        $this->assertEquals(
            $redirect,
            $utils->getAuthorizationUrl($request, 'instagram')
        );

        $this->assertEquals(
            'instagram',
            $request->attributes->get('service')
        );
    }

    public function testGetAuthorizationUrlWithStateQueryParameters()
    {
        $parameters = ['foo' => 'bar', 'foobar' => 'foobaz'];
        $state = new State($parameters);

        $url = 'http://localhost:8080/login/check-instagram';
        $redirect = 'https://api.instagram.com/oauth/authorize?redirect='.rawurlencode($url);

        $request = $this->getRequest($url.'?state='.$state->encode());
        $resource = $this->getMockBuilder(ResourceOwnerInterface::class)->getMock();

        $utils = new OAuthUtils($this->getHttpUtils($url), $this->getAutorizationChecker(false, $this->grantRule), true, $this->grantRule);
        $utils->addResourceOwnerMap($this->getMap($url, $redirect, false, false, $resource));

        $resource->expects($this->exactly(2))
            ->method('addStateParameter')
            ->withConsecutive(['foo', 'bar'], ['foobar', 'foobaz']);

        $this->assertEquals(
            $redirect,
            $utils->getAuthorizationUrl($request, 'instagram')
        );
    }

    public function testGetAuthorizationUrlWithoutUserToken()
    {
        $url = 'http://localhost:8080/login/check-instagram';
        $request = $this->getRequest($url);
        $redirect = 'https://api.instagram.com/oauth/authorize?redirect='.rawurlencode($url);

        $utils = new OAuthUtils($this->getHttpUtils($url), $this->getAutorizationChecker(false, $this->grantRule), true, $this->grantRule);
        $utils->addResourceOwnerMap($this->getMap($url, $redirect));

        $this->assertEquals(
            $redirect,
            $utils->getAuthorizationUrl($request, 'instagram')
        );

        $this->assertNull($request->attributes->get('service'));
    }

    public function testGetAuthorizationUrlWithAuthenticatedFullyRule()
    {
        $url = 'http://localhost:8080/login/check-instagram';
        $request = $this->getRequest($url);
        $redirect = 'https://api.instagram.com/oauth/authorize?redirect='.rawurlencode($url);

        $utils = new OAuthUtils(
            $this->getHttpUtils($url),
            $this->getAutorizationChecker(false, 'IS_AUTHENTICATED_FULLY'),
            true,
            'IS_AUTHENTICATED_FULLY'
        );
        $utils->addResourceOwnerMap($this->getMap($url, $redirect));

        $this->assertEquals(
            $redirect,
            $utils->getAuthorizationUrl($request, 'instagram')
        );

        $this->assertNull($request->attributes->get('service'));
    }

    /**
     * @dataProvider provideValidData
     *
     * @param string $signature
     * @param string $url
     */
    public function testSignatureIsGeneratedCorrectly($signature, $url)
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

    /**
     * @dataProvider provideInvalidData
     *
     * @param array $parameters
     */
    public function testThrowsExceptionIfRequiredParameterIsMissing($parameters)
    {
        $this->expectException(\RuntimeException::class);

        OAuthUtils::signRequest('GET', 'http://example.com', $parameters, 'client_secret');
    }

    public function testGetLoginUrlWithStateQueryParameters()
    {
        $url = 'http://localhost:8080/instagram';

        $authChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $resourceOwner = $this->createMock(ResourceOwnerInterface::class);
        $mapMock = $this->createMock(ResourceOwnerMap::class);
        $mapMock
            ->expects($this->any())
            ->method('getResourceOwnerByName')
            ->with('instagram')
            ->willReturn($resourceOwner);

        $utils = new OAuthUtils($this->getHttpUtils($url), $authChecker, true, $this->grantRule);
        $utils->addResourceOwnerMap($mapMock);

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

    public function provideValidData()
    {
        return [
            ['iflJZCKxEsZ58FFDyCysxfLbuKM=', 'http://photos.example.net/photos'],
            ['tR3+Ty81lMeYAr/Fid0kMTYa/WM=', 'http://photos.example.net/photos?file=vacation.jpg&size=original'],
        ];
    }

    public function provideInvalidData()
    {
        return [
            ['oauth_timestamp' => '', 'oauth_nonce' => '', 'oauth_version' => '', 'oauth_signature_method' => ''],
            ['oauth_consumer_key' => '', 'oauth_nonce' => '', 'oauth_version' => '', 'oauth_signature_method' => ''],
            ['oauth_consumer_key' => '', 'oauth_timestamp' => '', 'oauth_version' => '', 'oauth_signature_method' => ''],
            ['oauth_consumer_key' => '', 'oauth_timestamp' => '', 'oauth_nonce' => '', 'oauth_signature_method' => ''],
            ['oauth_consumer_key' => '', 'oauth_timestamp' => '', 'oauth_nonce' => '', 'oauth_version' => ''],
        ];
    }

    private function getRequest($url)
    {
        return Request::create($url, 'get', [], [], [], ['SERVER_PORT' => 8080]);
    }

    private function getMap($url, $redirect, $hasUser = false, $hasOneRedirectUrl = false, $resource = null)
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

        $mapMock = $this->createMock(ResourceOwnerMap::class);

        $mapMock
            ->expects($this->once())
            ->method('getResourceOwnerByName')
            ->with('instagram')
            ->willReturn($resource);

        if (!$hasUser && !$hasOneRedirectUrl) {
            $mapMock
                ->expects($this->once())
                ->method('getResourceOwnerCheckPath')
                ->with('instagram')
                ->willReturn('/login/check-instagram');
        }

        if ($hasUser) {
            $resource
                ->expects($this->once())
                ->method('getName')
                ->willReturn('instagram');
        }

        return $mapMock;
    }

    private function getHttpUtils($generatedUrl = '/')
    {
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);

        $urlGenerator
            ->expects($this->any())
            ->method('generate')
            ->willReturn(($generatedUrl))
        ;

        return new HttpUtils($urlGenerator);
    }

    private function getAutorizationChecker($hasUser, $grantRule)
    {
        $mock = $this->createMock(AuthorizationCheckerInterface::class);
        $mock->expects($this->once())
            ->method('isGranted')
            ->with($grantRule)
            ->willReturn($hasUser);

        return $mock;
    }
}
