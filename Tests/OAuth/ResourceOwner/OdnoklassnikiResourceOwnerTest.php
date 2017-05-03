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
use Symfony\Component\Security\Http\HttpUtils;

class OdnoklassnikiResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected $resourceOwnerClass = OdnoklassnikiResourceOwner::class;
    protected $userResponse = <<<json
{
    "uid": "1",
    "username": "bar"
}
json;

    protected $paths = array(
        'identifier' => 'uid',
        'nickname' => 'username',
        'realname' => 'name',
        'email' => 'email',
        'firstname' => 'first_name',
        'lastname' => 'last_name',
    );

    protected function setUpResourceOwner($name, HttpUtils $httpUtils, array $options)
    {
        return parent::setUpResourceOwner(
            $name,
            $httpUtils,
            array_merge(
                array(
                    'application_key' => '123456',
                ),
                $options
            )
        );
    }
}
