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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\YandexResourceOwner;
use Symfony\Component\HttpKernel\Kernel;

class YandexResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected $userResponse = '{"id": "bar"}';

    public function setUp()
    {
        $this->resourceOwner = $this->createResourceOwner($this->getDefaultOptions(), 'oauth2');
    }

    protected function getDefaultOptions()
    {
        return array(
            'client_id'     => 'clientid',
            'client_secret' => 'clientsecret',
        );
    }

    protected function createResourceOwner(array $options, $name, $paths = null)
    {
        $this->buzzClient = $this->getMockBuilder('\Buzz\Client\ClientInterface')
            ->disableOriginalConstructor()->getMock();
        $httpUtils = $this->getMockBuilder('\Symfony\Component\Security\Http\HttpUtils')
            ->disableOriginalConstructor()->getMock();

        return new YandexResourceOwner($this->buzzClient, $httpUtils, $options, $name);
    }

    public function testGetAuthorizationUrl()
    {
        $this->assertEquals(
            'https://oauth.yandex.ru/authorize?response_type=code&client_id=clientid&scope=&redirect_uri=http%3A%2F%2Fredirect.to%2F',
            $this->resourceOwner->getAuthorizationUrl('http://redirect.to/')
        );
    }

    public function testGetOption()
    {
        $this->assertEquals('https://login.yandex.ru/info?format=json', $this->resourceOwner->getOption('infos_url'));
    }
}
