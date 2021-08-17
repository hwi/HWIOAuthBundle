<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Tests\OAuth\ResourceOwner;

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\OdnoklassnikiResourceOwner;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface;
use Symfony\Component\Security\Http\HttpUtils;

class OdnoklassnikiResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected string $resourceOwnerClass = OdnoklassnikiResourceOwner::class;
    protected $userResponse = <<<json
{
    "uid": "1",
    "username": "bar"
}
json;

    protected array $paths = [
        'identifier' => 'uid',
        'nickname' => 'username',
        'realname' => 'name',
        'email' => 'email',
        'firstname' => 'first_name',
        'lastname' => 'last_name',
    ];

    protected function setUpResourceOwner(string $name, HttpUtils $httpUtils, array $options, array $responses): ResourceOwnerInterface
    {
        return parent::setUpResourceOwner(
            $name,
            $httpUtils,
            array_merge(
                [
                    'application_key' => '123456',
                ],
                $options
            ),
            $responses
        );
    }
}
