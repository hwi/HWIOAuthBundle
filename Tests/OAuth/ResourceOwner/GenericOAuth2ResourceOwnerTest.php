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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\GenericOAuth2ResourceOwner;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpFoundation\Request;

class GenericOAuth2ResourceOwnerTest extends \PHPUnit_Framework_Testcase
{
    protected $resourceOwner;
    protected $buzzClient;
    protected $buzzResponse;
    protected $buzzResponseContentType;

    protected $userResponse = '{"foo": "bar"}';

    public function setup()
    {
        $this->resourceOwner = $this->createResourceOwner($this->getDefaultOptions(), 'oauth2');
    }

    protected function getDefaultOptions()
    {
        return array(
            'infos_url' => 'http://user.info/',
            'client_id' => 'clientid',
            'authorization_url' => 'http://user.auth/',
            'access_token_url' => 'http://user.access/',
            'client_secret' => 'clientsecret',
        );
    }

    protected function getDefaultPaths()
    {
        return array(
            'username' => 'foo',
            'displayname' => 'foo_disp',
        );
    }

    protected function createResourceOwner(array $options, $name, $paths = null)
    {
        $this->buzzClient = $this->getMockBuilder('\Buzz\Client\ClientInterface')
            ->disableOriginalConstructor()->getMock();
        $httpUtils = $this->getMockBuilder('\Symfony\Component\Security\Http\HttpUtils')
            ->disableOriginalConstructor()->getMock();

        $resourceOwner = new GenericOAuth2ResourceOwner($this->buzzClient, $httpUtils, $options, $name);
        $resourceOwner->addPaths($paths ?: $this->getDefaultPaths());

        return $resourceOwner;
    }

    public function testGetOption()
    {
        $this->assertEquals('http://user.info/', $this->resourceOwner->getOption('infos_url'));
        $this->assertEquals('clientid', $this->resourceOwner->getOption('client_id'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetInvalidOptionThrowsException()
    {
        $this->resourceOwner->getOption('non_existing');
    }

    public function testGetUserInformation()
    {
        $this->markTestSkipped('Test will work from PHPUnit 3.7 onwards. See: https://github.com/sebastianbergmann/phpunit-mock-objects/issues/47.');
        $this->mockBuzz($this->userResponse);
        $userResponse = $this->resourceOwner->getUserInformation('access_token');

        $this->assertEquals('bar', $userResponse->getUsername());
    }

    public function testGetAuthorizationUrl()
    {
        $this->assertEquals(
            'http://user.auth/?response_type=code&client_id=clientid&scope=&redirect_uri=http%3A%2F%2Fredirect.to%2F',
            $this->resourceOwner->getAuthorizationUrl('http://redirect.to/')
        );
    }

    public function testGetAccessToken()
    {
        $this->markTestSkipped('Test will work from PHPUnit 3.7 onwards. See: https://github.com/sebastianbergmann/phpunit-mock-objects/issues/47.');
        $this->mockBuzz('access_token=code');
        $request = new Request(array('oauth_verifier' => 'code'));
        $accessToken = $this->resourceOwner->getAccessToken($request, 'http://redirect.to/');
    }

    public function testGetAccessTokenJsonResponse()
    {
        $this->markTestSkipped('Test will work from PHPUnit 3.7 onwards. See: https://github.com/sebastianbergmann/phpunit-mock-objects/issues/47.');
        $this->mockBuzz('{"access_token": "code"}', 'application/json');
        $request = new Request(array('oauth_verifier' => 'code'));
        $accessToken = $this->resourceOwner->getAccessToken($request, 'http://redirect.to/');
    }

    public function testGetAccessTokenJsonCharsetResponse()
    {
        $this->markTestSkipped('Test will work from PHPUnit 3.7 onwards. See: https://github.com/sebastianbergmann/phpunit-mock-objects/issues/47.');
        $this->mockBuzz('{"access_token": "code"}', 'application/json; charset=utf-8');
        $request = new Request(array('oauth_verifier' => 'code'));
        $accessToken = $this->resourceOwner->getAccessToken($request, 'http://redirect.to/');
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AuthenticationException
     */
    public function testGetAccessTokenFailedResponse()
    {
        $this->mockBuzz('invalid');
        $request = new Request(array('oauth_verifier' => 'code'));
        $accessToken = $this->resourceOwner->getAccessToken($request, 'http://redirect.to/');
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AuthenticationException
     */
    public function testGetAccessTokenErrorResponse()
    {
        $this->mockBuzz('error=foo');
        $request = new Request(array('oauth_verifier' => 'code'));
        $accessToken = $this->resourceOwner->getAccessToken($request, 'http://redirect.to/');
    }

    public function testGetSetName()
    {
        $this->assertEquals('oauth2', $this->resourceOwner->getName());
        $this->resourceOwner->setName('foo');
        $this->assertEquals('foo', $this->resourceOwner->getName());
    }

    public function testCustomResponseClass()
    {
        $options = $this->getDefaultOptions();
        $options['user_response_class'] = '\HWI\Bundle\OAuthBundle\Tests\Fixtures\CustomUserResponse';
        $resourceOwner = $this->createResourceOwner($options, 'oauth2');

        $this->mockBuzz();
        $userResponse = $resourceOwner->getUserInformation('access_token');

        $this->assertInstanceOf($options['user_response_class'], $userResponse);
        $this->assertEquals('foo', $userResponse->getUsername());
    }

    protected function mockBuzz($response = '', $contentType = 'text/plain')
    {
        $this->buzzClient->expects($this->once())
            ->method('send')
            ->will($this->returnCallback(array($this, 'buzzSendMock')));
        $this->buzzResponse = $response;
        $this->buzzResponseContentType = $contentType;
    }

    public function buzzSendMock($request, $response)
    {
        $response->setContent($this->buzzResponse);
        $response->addHeader('Content-Type: ' . $this->buzzResponseContentType);
    }
}
