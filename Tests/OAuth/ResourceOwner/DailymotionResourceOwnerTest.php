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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\DailymotionResourceOwner;

class DailymotionResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected $userResponse = <<<json
{
    "id": "1",
    "screenname": "bar"
}
json;

    protected $paths = array(
        'identifier' => 'id',
        'nickname'   => 'screenname',
        'realname'   => 'fullname'
    );

    public function testDisplayPopup()
    {
        $resourceOwner = $this->createResourceOwner('facebook', array('display' => 'popup'));
        $this->assertEquals('popup', $resourceOwner->getOption('display'));
        $this->assertEquals(
            $this->options['authorization_url'] . '&response_type=code&client_id=clientid&state=random&redirect_uri=http%3A%2F%2Fredirect.to%2F&display=popup',
            $resourceOwner->getAuthorizationUrl('http://redirect.to/')
        );
    }
    
    protected function setUpResourceOwner($name, $httpUtils, array $options)
    {
        $options = array_merge(
            array(
                'authorization_url' => 'https://api.dailymotion.com/oauth/authorize',
                'access_token_url'  => 'https://api.dailymotion.com/oauth/token',
                'infos_url'         => 'https://api.dailymotion.com/me',
            ),
            $options
        );

        return new DailymotionResourceOwner($this->buzzClient, $httpUtils, $options, $name, $this->storage);
    }
}
