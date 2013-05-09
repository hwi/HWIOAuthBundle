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
use Symfony\Component\HttpFoundation\Request;

class OdniklassnikiResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected $userResponse = <<<json
{
    "id": "1",
    "username": "bar"
}
json;

    protected $paths = array(
        'identifier' => 'id',
        'nickname'   => 'username',
        'realname'   => 'name',
    );

    protected function setUpResourceOwner($name, $httpUtils, array $options)
    {
        $options = array_merge(
            array(
                'authorization_url'         => 'http://www.odnoklassniki.ru/oauth/authorize',
                'access_token_url'          => 'http://api.odnoklassniki.ru/oauth/token.do',
                'infos_url'                 => 'http://api.odnoklassniki.ru/fb.do?method=users.getCurrentUser',

                'odnoklassniki_app_key'     => '123456',
            ),
            $options
        );

        return new OdnoklassnikiResourceOwner($this->buzzClient, $httpUtils, $options, $name);
    }
}
