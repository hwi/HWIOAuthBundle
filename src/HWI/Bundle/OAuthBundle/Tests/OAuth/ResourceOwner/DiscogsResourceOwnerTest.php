<?php

namespace HWI\Bundle\OAuthBundle\Tests\OAuth\ResourceOwner;

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\DiscogsResourceOwner;

class DiscogsResourceOwnerTest extends GenericOAuth1ResourceOwnerTest
{
    protected $userResponse = <<<json
{
  "id": 1,
  "username": "bar",
  "resource_url": "http://api.discogs.com/users/bar",
  "consumer_name": "Your Application Name"
}
json;
    protected $paths = array(
        'identifier' => 'id',
        'nickname'   => 'username',
    );

    protected function setUpResourceOwner($name, $httpUtils, array $options)
    {
        return new DiscogsResourceOwner($this->buzzClient, $httpUtils, $options, $name, $this->storage);
    }
} 
