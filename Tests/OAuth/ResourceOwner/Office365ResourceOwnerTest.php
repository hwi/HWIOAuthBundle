<?php

namespace HWI\Bundle\OAuthBundle\Tests\OAuth\ResourceOwner;


use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\Office365ResourceOwner;

class Office365ResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected function setUpResourceOwner($name, $httpUtils, array $options)
    {
        return new Office365ResourceOwner($this->buzzClient, $httpUtils, $options, $name, $this->storage);
    }

}