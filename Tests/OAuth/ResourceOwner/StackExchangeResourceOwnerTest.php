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
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface;
use HWI\Bundle\OAuthBundle\Test\OAuth\ResourceOwner\GenericOAuth2ResourceOwnerTestCase;
use Symfony\Component\Security\Http\HttpUtils;

final class StackExchangeResourceOwnerTest extends GenericOAuth2ResourceOwnerTestCase
{
    protected string $resourceOwnerClass = StackExchangeResourceOwner::class;
    protected string $userResponse = <<<json
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

    protected array $paths = [
        'identifier' => 'items.0.user_id',
        'nickname' => 'items.0.display_name',
        'realname' => 'items.0.display_name',
        'profilepicture' => 'items.0.profile_image',
    ];

    protected string $authorizationUrlBasePart = 'http://user.auth/?test=2&response_type=code&client_id=clientid&scope=no_expiry';

    protected function setUpResourceOwner(string $name, HttpUtils $httpUtils, array $options, array $responses): ResourceOwnerInterface
    {
        return parent::setUpResourceOwner(
            $name,
            $httpUtils,
            array_merge($options, ['key' => 'baz']),
            $responses
        );
    }
}
