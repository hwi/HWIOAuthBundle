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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\LeadAuthResourceOwner;

class LeadAuthResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected $userResponse = <<<json
{
  "_id": "1",
  "profile": {
    "display_name": "Name Surname",
    "email": {
      "value": "bar"
    },
    "photo": "https://example.com/qweqwe.jpg"
  },
  "username": "bar",
  "providers": [
    {
      "name": "username-password",
      "profile": {
        "displayName": "Name Surname",
        "emails": [
          {
            "value": "bar"
          }
        ]
      }
    }
  ]
}
json;

    protected $paths = array(
        'identifier'     => '_id',
        'nickname'       => 'username',
        'realname'       => 'profile.display_name',
        'email'          => 'profile.email.value',
        'profilepicture' => 'profile.photo',
    );

    protected function setUpResourceOwner($name, $httpUtils, array $options)
    {
        $options = array_merge(
            array(
                'account' => 'my-account'
            ),
            $options
        );

        return new LeadAuthResourceOwner($this->buzzClient, $httpUtils, $options, $name, $this->storage);
    }
}
