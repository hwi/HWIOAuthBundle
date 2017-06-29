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

use Http\Client\Exception\TransferException;
use HWI\Bundle\OAuthBundle\OAuth\Exception\HttpTransportException;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\AzureResourceOwner;
use HWI\Bundle\OAuthBundle\Tests\Fixtures\CustomUserResponse;
use Symfony\Component\Security\Http\HttpUtils;

class AzureResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected $resourceOwnerClass = AzureResourceOwner::class;
    protected $csrf = true;

    protected $userResponse = <<<json
{
    "sub": "1",
    "given_name": "Dummy",
    "family_name": "Tester",
    "name": "Dummy Tester",
    "unique_name": "dummy123"
}
json;

    protected $paths = array(
        'identifier' => 'sub',
        'nickname' => 'unique_name',
        'realname' => array('given_name', 'family_name'),
        'email' => array('upn', 'email'),
        'profilepicture' => null,
    );

    protected $expectedUrls = array(
        'authorization_url_csrf' => 'http://user.auth/?test=2&response_type=code&client_id=clientid&state=random&redirect_uri=http%3A%2F%2Fredirect.to%2F&resource=https%3A%2F%2Fgraph.windows.net',
    );

    public function testGetAuthorizationUrl()
    {
        $this->assertEquals(
            $this->options['authorization_url'].'&response_type=code&client_id=clientid&state=random&redirect_uri=http%3A%2F%2Fredirect.to%2F&resource=https%3A%2F%2Fgraph.windows.net',
            $this->resourceOwner->getAuthorizationUrl('http://redirect.to/')
        );
    }

    public function testGetUserInformation()
    {
        $token = '.'.base64_encode($this->userResponse);
        /**
         * @var \HWI\Bundle\OAuthBundle\OAuth\Response\AbstractUserResponse
         */
        $userResponse = $this->resourceOwner->getUserInformation(array(
            'access_token' => 'token',
            'id_token' => $token,
        ));

        $this->assertEquals('1', $userResponse->getUsername());
        $this->assertEquals('Dummy Tester', $userResponse->getRealName());
        $this->assertEquals('dummy123', $userResponse->getNickname());
        $this->assertNull($userResponse->getRefreshToken());
        $this->assertNull($userResponse->getExpiresIn());
    }

    public function testCustomResponseClass()
    {
        $class = CustomUserResponse::class;
        $resourceOwner = $this->createResourceOwner($this->resourceOwnerName, array('user_response_class' => $class));

        $token = '.'.base64_encode($this->userResponse);

        /**
         * @var \HWI\Bundle\OAuthBundle\OAuth\Response\AbstractUserResponse
         */
        $userResponse = $resourceOwner->getUserInformation(array(
            'access_token' => 'token',
            'id_token' => $token,
        ));

        $this->assertInstanceOf($class, $userResponse);
        $this->assertEquals('foo666', $userResponse->getUsername());
        $this->assertEquals('foo', $userResponse->getNickname());
        $this->assertEquals('token', $userResponse->getAccessToken());
        $this->assertNull($userResponse->getRefreshToken());
        $this->assertNull($userResponse->getExpiresIn());
    }

    public function testGetUserInformationFailure()
    {
        $exception = new TransferException();

        $this->httpClient->expects($this->once())
            ->method('send')
            ->will($this->throwException($exception));

        $token = '.'.base64_encode($this->userResponse);

        try {
            $this->resourceOwner->getUserInformation(array('access_token' => 'token', 'id_token' => $token));
            $this->fail('An exception should have been raised');
        } catch (HttpTransportException $e) {
            $this->assertSame($exception, $e->getPrevious());
        }
    }

    protected function setUpResourceOwner($name, HttpUtils $httpUtils, array $options)
    {
        return parent::setUpResourceOwner(
            $name,
            $httpUtils,
            array_merge(
                array(
                    'resource' => 'https://graph.windows.net',
                ),
                $options
            )
        );
    }
}
