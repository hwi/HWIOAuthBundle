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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\FlickrResourceOwner;

class FlickrResourceOwnerTest extends GenericOAuth1ResourceOwnerTest
{
    protected $userResponse = '{"username": "bar"}';

    public function setUp()
    {
        $this->resourceOwner = $this->createResourceOwner($this->getDefaultOptions(), 'oauth1');
    }

    protected function getDefaultOptions()
    {
        return array(
            'client_id' => 'clientid',
            'client_secret' => 'clientsecret',
        );
    }

    protected function createResourceOwner(array $options, $name, $paths = null)
    {
        $this->buzzClient = $this->getMockBuilder('\Buzz\Client\ClientInterface')
            ->disableOriginalConstructor()->getMock();
        $httpUtils = $this->getMockBuilder('\Symfony\Component\Security\Http\HttpUtils')
            ->disableOriginalConstructor()->getMock();

        $this->storage = $this->getMock('\HWI\Bundle\OAuthBundle\OAuth\OAuth1RequestTokenStorageInterface');

        return new FlickrResourceOwner($this->buzzClient, $httpUtils, $options, $name, $this->storage);
    }

    public function testGetAuthorizationUrl()
    {
        $this->markTestSkipped('Test will work from PHPUnit 3.7 onwards. See: https://github.com/sebastianbergmann/phpunit-mock-objects/issues/47.');
        $this->mockBuzz('{"oauth_token": "token","oauth_token_secret": "token_secret"}', 'application/json; charset=utf-8');
        $this->assertEquals(
            'http://www.flickr.com/services/oauth/authorize?oauth_token=token&perms=read',
            $this->resourceOwner->getAuthorizationUrl('http://redirect.to/')
        );
    }

    public function testCustomResponseClass()
    {
        $this->markTestSkipped('This step require to implement mock of storage.');
    }

    public function testGetOption()
    {
        $this->assertEquals('http://api.flickr.com/services/rest', $this->resourceOwner->getOption('infos_url'));
    }
}
