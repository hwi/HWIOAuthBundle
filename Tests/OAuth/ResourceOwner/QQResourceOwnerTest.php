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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\QQResourceOwner;
use HWI\Bundle\OAuthBundle\Tests\Fixtures\CustomUserResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\HttpUtils;

class QQResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected $resourceOwnerClass = QQResourceOwner::class;
    protected $userResponse = <<<json
{
    "ret": 0,
    "openid": "1",
    "nickname": "bar",
    "figureurl_qq_1": "http://q.qlogo.cn/qqapp/100312990/DE1931D5330620DBD07FB4A5422917B6/40"
}
json;
    protected $paths = array(
        'identifier' => 'openid',
        'nickname' => 'nickname',
        'realname' => 'nickname',
        'profilepicture' => 'figureurl_qq_1',
    );

    /**
     * {@inheritdoc}
     */
    public function testGetUserInformation()
    {
        $this->mockHttpClient($this->userResponse);

        /**
         * @var \HWI\Bundle\OAuthBundle\OAuth\Response\AbstractUserResponse
         */
        $userResponse = $this->resourceOwner->getUserInformation(array('access_token' => 'token'), array('openid' => '1'));

        $this->assertEquals('1', $userResponse->getUsername());
        $this->assertEquals('bar', $userResponse->getNickname());
        $this->assertEquals('token', $userResponse->getAccessToken());
        $this->assertNull($userResponse->getRefreshToken());
        $this->assertNull($userResponse->getExpiresIn());
    }

    /**
     * {@inheritdoc}
     */
    public function testCustomResponseClass()
    {
        $class = CustomUserResponse::class;
        $resourceOwner = $this->createResourceOwner('oauth2', array('user_response_class' => $class));

        $this->mockHttpClient('{"ret": 0}');

        /** @var $userResponse CustomUserResponse */
        $userResponse = $resourceOwner->getUserInformation(array('access_token' => 'token'), array('openid' => '1'));

        $this->assertInstanceOf($class, $userResponse);
        $this->assertEquals('foo666', $userResponse->getUsername());
        $this->assertEquals('foo', $userResponse->getNickname());
        $this->assertEquals('token', $userResponse->getAccessToken());
        $this->assertNull($userResponse->getRefreshToken());
        $this->assertNull($userResponse->getExpiresIn());
    }

    /**
     * QQ returns access token in jsonp format.
     */
    public function testGetAccessTokenJsonpResponse()
    {
        $this->mockHttpClient('callback({"access_token": "code"});');

        $request = new Request(array('code' => 'somecode'));

        $this->assertEquals(
            array('access_token' => 'code'),
            $this->resourceOwner->getAccessToken($request, 'http://redirect.to/')
        );
    }

    /**
     * QQ returns errors in jsonp format.
     *
     * @expectedException \Symfony\Component\Security\Core\Exception\AuthenticationException
     */
    public function testGetAccessTokenErrorResponse()
    {
        $this->mockHttpClient('callback({"error": 1, "msg": "error"})');

        $request = new Request(array('code' => 'code'));

        $this->resourceOwner->getAccessToken($request, 'http://redirect.to/');
    }

    protected function setUpResourceOwner($name, HttpUtils $httpUtils, array $options)
    {
        return parent::setUpResourceOwner(
            $name,
            $httpUtils,
            array_merge(
                array(
                    'authorization_url' => 'https://graph.qq.com/oauth2.0/authorize?format=json',
                    'access_token_url' => 'https://graph.qq.com/oauth2.0/token',
                    'infos_url' => 'https://graph.qq.com/user/get_user_info',
                    'me_url' => 'https://graph.qq.com/oauth2.0/me',
                ),
                $options
            )
        );
    }
}
