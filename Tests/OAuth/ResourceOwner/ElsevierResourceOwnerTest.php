<?php

namespace HWI\Bundle\OAuthBundle\Tests\OAuth\ResourceOwner;

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\ElsevierResourceOwner;

class ElsevierResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected $expectedUrls = array(
        'authorization_url'      => 'http://user.auth/?test=2&response_type=code&client_id=clientid&elsevier_targetAppName=clientsecret&redirect_uri=http%3A%2F%2Fredirect.to%2F%s',
        'authorization_url_csrf' => 'http://user.auth/?test=2&response_type=code&client_id=clientid&elsevier_targetAppName=clientsecret&redirect_uri=http%3A%2F%2Fredirect.to%2F%s',
    );

    public function testGetUserInformation()
    {
        /**
         * Since Elsevier API does not provide any entry point for user
         * information retrieval, we could not and should not test that.
         */
        $this->markTestSkipped(); 
    }

    protected function setUpResourceOwner($name, $httpUtils, array $options)
    {
        return new ElsevierResourceOwner($this->buzzClient, $httpUtils, $options, $name, $this->storage);
    }
}
