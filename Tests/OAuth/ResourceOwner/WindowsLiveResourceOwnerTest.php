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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\WindowsLiveResourceOwner;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpFoundation\Request;

class WindowsLiveResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected $userResponse = '{"id": "bar"}';

    public function setup()
    {
        $this->resourceOwner = $this->createResourceOwner($this->getDefaultOptions(), 'oauth2');
    }

    protected function getDefaultOptions()
    {
        return array('client_id' => 'clientid',
            'client_secret' => 'clientsecret',
        );
    }

    protected function createResourceOwner(array $options, $name, $paths = null)
    {
        $this->buzzClient = $this->getMockBuilder('\Buzz\Client\ClientInterface')
            ->disableOriginalConstructor()->getMock();
        $httpUtils = $this->getMockBuilder('\Symfony\Component\Security\Http\HttpUtils')
            ->disableOriginalConstructor()->getMock();

        return new WindowsLiveResourceOwner($this->buzzClient, $httpUtils, $options, $name);
    }

    public function testGetAuthorizationUrl()
    {
        $this->assertEquals(
            'https://login.live.com/oauth20_authorize.srf?response_type=code&client_id=clientid&scope=&redirect_uri=http%3A%2F%2Fredirect.to%2F',
            $this->resourceOwner->getAuthorizationUrl('http://redirect.to/')
        );
    }

    public function testGetOption()
    {
        $this->assertEquals('https://apis.live.net/v5.0/me', $this->resourceOwner->getOption('infos_url'));
    }

    public function testGetAccessToken()
    {
        $this->markTestSkipped('Test will work from PHPUnit 3.7 onwards. See: https://github.com/sebastianbergmann/phpunit-mock-objects/issues/47.');
        $this->mockBuzz('{"access_token": "code"}', 'application/json');
        $request = new Request(array('oauth_verifier' => 'code'));
        $accessToken = $this->resourceOwner->getAccessToken($request, 'http://redirect.to/');
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AuthenticationException
     */
    public function testGetAccessTokenErrorResponse()
    {
        $this->mockBuzz('{"error": "foo"}');
        $request = new Request(array('oauth_verifier' => 'code'));
        $accessToken = $this->resourceOwner->getAccessToken($request, 'http://redirect.to/');
    }
}
