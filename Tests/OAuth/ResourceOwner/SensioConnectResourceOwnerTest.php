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

use Buzz\Browser;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\SensioConnectResourceOwner;

class SensioConnectResourceOwnerTest extends \PHPUnit_Framework_Testcase
{
    public function setUp()
    {
        $this->resourceOwner = $this->createResourceOwner($this->getDefaultOptions(), 'generic');
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

        return new SensioConnectResourceOwner($this->buzzClient, $httpUtils, $options, $name);
    }

    public function testGetOption()
    {
        $this->assertEquals('https://connect.sensiolabs.com/api', $this->resourceOwner->getOption('infos_url'));
    }
}
