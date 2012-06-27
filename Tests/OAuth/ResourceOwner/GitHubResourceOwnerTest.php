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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\GitHubResourceOwner;
use Symfony\Component\HttpKernel\Kernel;

class GitHubResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected $userResponse = '{"login": "bar"}';

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
        // Session changed interface in 2.1, hack to avoid branching
        if (version_compare(Kernel::VERSION, '2.1-DEV', '>=')) {
            $session = new \Symfony\Component\HttpFoundation\Session\Session(new \Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage());
        } else {
            $session = new \Symfony\Component\HttpFoundation\Session(new \Symfony\Component\HttpFoundation\SessionStorage\ArraySessionStorage());
        }

        return new GitHubResourceOwner($this->buzzClient, $httpUtils, $session, $options, $name);
    }

    public function testGetAuthorizationUrl()
    {
        $this->assertEquals(
            'https://github.com/login/oauth/authorize?response_type=code&client_id=clientid&scope=&redirect_uri=http%3A%2F%2Fredirect.to%2F',
            $this->resourceOwner->getAuthorizationUrl('http://redirect.to/')
        );
    }

    public function testGetOption()
    {
        $this->assertEquals('https://api.github.com/user', $this->resourceOwner->getOption('infos_url'));
    }
}
