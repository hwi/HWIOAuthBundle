<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Tests\Security;

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface;
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

        $authorizationChecker = $this->getMockBuilder(AuthorizationCheckerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

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
        $utils->addResourceOwnerMap($this->getMap($url, $redirect, true));

        $this->assertEquals(
            $redirect,
            $utils->getAuthorizationUrl($request, 'instagram')
        );

        $this->assertEquals(
            'instagram',
            $request->attributes->get('service')
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
        $parameters = array(
            'oauth_consumer_key' => 'dpf43f3p2l4k3l03',
            'oauth_token' => 'nnch734d00sl2jdk',
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => '1191242096',
            'oauth_nonce' => 'kllo9940pd9333jh',
            'oauth_version' => '1.0',
        );

        $this->assertEquals(
            $signature,
            OAuthUtils::signRequest('GET', $url, $parameters, 'kd94hf93k423kf44', 'pfkkdhi9sl3r4s00')
        );
    }

    /**
     * @dataProvider provideInvalidData
     * @expectedException \RuntimeException
     *
     * @param array $parameters
     */
    public function testThrowsExceptionIfRequiredParameterIsMissing($parameters)
    {
        OAuthUtils::signRequest('GET', 'http://example.com', $parameters, 'client_secret');
    }

    public function provideValidData()
    {
        return array(
            array('iflJZCKxEsZ58FFDyCysxfLbuKM=', 'http://photos.example.net/photos'),
            array('tR3+Ty81lMeYAr/Fid0kMTYa/WM=', 'http://photos.example.net/photos?file=vacation.jpg&size=original'),
        );
    }

    public function provideInvalidData()
    {
        return array(
            array('oauth_timestamp' => '', 'oauth_nonce' => '', 'oauth_version' => '', 'oauth_signature_method' => ''),
            array('oauth_consumer_key' => '', 'oauth_nonce' => '', 'oauth_version' => '', 'oauth_signature_method' => ''),
            array('oauth_consumer_key' => '', 'oauth_timestamp' => '', 'oauth_version' => '', 'oauth_signature_method' => ''),
            array('oauth_consumer_key' => '', 'oauth_timestamp' => '', 'oauth_nonce' => '', 'oauth_signature_method' => ''),
            array('oauth_consumer_key' => '', 'oauth_timestamp' => '', 'oauth_nonce' => '', 'oauth_version' => ''),
        );
    }

    private function getRequest($url)
    {
        return Request::create($url, 'get', array(), array(), array(), array('SERVER_PORT' => 8080));
    }

    private function getMap($url, $redirect, $hasUser = false, $hasOneRedirectUrl = false)
    {
        $resource = $this->getMockBuilder(ResourceOwnerInterface::class)
            ->getMock();

        $resource
            ->expects($this->once())
            ->method('getAuthorizationUrl')
            ->with($url, array())
            ->will($this->returnValue($redirect));

        $resource
            ->expects($this->any())
            ->method('getOption')
            ->with('auth_with_one_url')
            ->will($this->returnValue($hasOneRedirectUrl));

        $mapMock = $this->getMockBuilder(ResourceOwnerMap::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mapMock
            ->expects($this->once())
            ->method('getResourceOwnerByName')
            ->with('instagram')
            ->will($this->returnValue($resource));

        if (!$hasUser && !$hasOneRedirectUrl) {
            $mapMock
                ->expects($this->once())
                ->method('getResourceOwnerCheckPath')
                ->with('instagram')
                ->will($this->returnValue('/login/check-instagram'));
        }

        if ($hasUser) {
            $resource
                ->expects($this->once())
                ->method('getName')
                ->will($this->returnValue('instagram'));
        }

        return $mapMock;
    }

    private function getHttpUtils($generatedUrl = '/')
    {
        $urlGenerator = $this->getMockBuilder(UrlGeneratorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $urlGenerator
            ->expects($this->any())
            ->method('generate')
            ->will($this->returnValue($generatedUrl))
        ;

        return new HttpUtils($urlGenerator);
    }

    private function getAutorizationChecker($hasUser, $grantRule)
    {
        $mock = $this->getMockBuilder(AuthorizationCheckerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mock->expects($this->once())
            ->method('isGranted')
            ->with($grantRule)
            ->will($this->returnValue($hasUser));

        return $mock;
    }
}
