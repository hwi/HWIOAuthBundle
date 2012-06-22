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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\GoogleResourceOwner;

class GoogleResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
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

        return new GoogleResourceOwner($this->buzzClient, $httpUtils, $options, $name);
    }

    public function testGetAuthorizationUrl()
    {
        $this->assertEquals(
            'https://accounts.google.com/o/oauth2/auth?response_type=code&client_id=clientid&scope=userinfo.profile&redirect_uri=http%3A%2F%2Fredirect.to%2F',
            $this->resourceOwner->getAuthorizationUrl('http://redirect.to/')
        );
    }

    public function testGetOption()
    {
        $this->assertEquals('https://www.googleapis.com/oauth2/v1/userinfo', $this->resourceOwner->getOption('infos_url'));
    }

    public function testGetAccessToken()
    {
        $this->markTestSkipped('Test will work from PHPUnit 3.7 onwards. See: https://github.com/sebastianbergmann/phpunit-mock-objects/issues/47.');
        $this->mockBuzz('{"access_token": "code"}', 'application/json');
        $accessToken = $this->resourceOwner->getAccessToken('code', 'http://redirect.to/');
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AuthenticationException
     */
    public function testGetAccessTokenErrorResponse()
    {
        $this->mockBuzz('{"error": "foo"}');
        $accessToken = $this->resourceOwner->getAccessToken('code', 'http://redirect.to/');
    }
}
