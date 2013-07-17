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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\OdnoklassnikiResourceOwner;

class OdnoklassnikiResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected $userResponse = <<<json
{
    "uid": "1",
    "username": "bar"
}
json;

    protected $paths = array(
        'identifier' => 'uid',
        'nickname'   => 'username',
        'realname'   => 'name',
    );

    public function testGetOptionAppKey()
    {
        $this->assertEquals('123456', $this->resourceOwner->getOption('application_key'));
    }

    protected function setUpResourceOwner($name, $httpUtils, array $options)
    {
        $options = array_merge(
            array(
                'authorization_url' => 'http://www.odnoklassniki.ru/oauth/authorize',
                'access_token_url'  => 'http://api.odnoklassniki.ru/oauth/token.do',
                'infos_url'         => 'http://api.odnoklassniki.ru/fb.do?method=users.getCurrentUser',

                'application_key'   => '123456',
            ),
            $options
        );

        return new OdnoklassnikiResourceOwner($this->buzzClient, $httpUtils, $options, $name);
    }
}
