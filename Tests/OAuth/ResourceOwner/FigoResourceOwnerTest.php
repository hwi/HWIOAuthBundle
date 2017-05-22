<?php

namespace HWI\Bundle\OAuthBundle\Tests\OAuth\ResourceOwner;

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\FigoResourceOwner;
use Symfony\Component\Security\Http\HttpUtils;

/**
 * Class FigoResourceOwnerTest.
 */
class FigoResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    /**
     * @var string
     */
    protected $userResponse = <<<json
{
    "user_id": "1",
    "name": "bar",
    "email": "baz"
}
json;

    /**
     * @var array
     */
    protected $paths = [
        'identifier' => 'user_id',
        'nickname' => 'name',
        'realname' => 'name',
        'email' => 'email',
    ];

    /**
     * @var array
     */
    protected $expectedUrls = [
        'authorization_url' => 'http://user.auth/?test=2&response_type=code&client_id=clientid&redirect_uri=http%3A%2F%2Fredirect.to%2F',
        'authorization_url_csrf' => 'http://user.auth/?test=2&response_type=code&client_id=clientid&state=random&redirect_uri=http%3A%2F%2Fredirect.to%2F',
    ];

    /**
     * @param string    $name
     * @param HttpUtils $httpUtils
     * @param array     $options
     *
     * @return FigoResourceOwner
     */
    protected function setUpResourceOwner($name, $httpUtils, array $options)
    {
        return new FigoResourceOwner($this->buzzClient, $httpUtils, $options, $name, $this->storage);
    }
}
