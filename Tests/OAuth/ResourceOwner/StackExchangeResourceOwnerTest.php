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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\StackExchangeResourceOwner;
use Symfony\Component\Security\Http\HttpUtils;

class StackExchangeResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected $resourceOwnerClass = StackExchangeResourceOwner::class;
    protected $userResponse = <<<json
{
   "items" : [
      {
         "display_name" : "bar",
         "profile_image" : "https://foo.com/bar.png",
         "user_id" : 1
      }
   ]
}
json;

    protected $paths = [
        'identifier' => 'items.0.user_id',
        'nickname' => 'items.0.display_name',
        'realname' => 'items.0.display_name',
        'profilepicture' => 'items.0.profile_image',
    ];

    protected $authorizationUrlBasePart = 'http://user.auth/?test=2&response_type=code&client_id=clientid&scope=no_expiry';

    protected function setUpResourceOwner($name, HttpUtils $httpUtils, array $options)
    {
        return parent::setUpResourceOwner(
            $name,
            $httpUtils,
            array_merge($options, ['key' => 'baz'])
        );
    }
}
