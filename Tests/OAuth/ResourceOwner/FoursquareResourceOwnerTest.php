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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\FoursquareResourceOwner;

class FoursquareResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected $userResponse = <<<json
{
    "response": {
        "user": {
            "id": "1",
            "firstName": "bar"
        }
    }
}
json;

    protected $paths = array(
        'identifier' => 'response.user.id',
        'nickname'   => 'response.user.firstName',
        'realname'   => 'response.user.lastName',
    );

    public function testGetOptionVersion()
    {
        $this->assertEquals('FAKE_VERSION', $this->resourceOwner->getOption('version'));
    }

    protected function setUpResourceOwner($name, $httpUtils, array $options)
    {
        $options = array_merge(
            array(
                 'authorization_url'   => 'https://foursquare.com/oauth2/authorize',
                 'access_token_url'    => 'https://foursquare.com/oauth2/access_token',
                 'infos_url'           => 'https://api.foursquare.com/v2/users/self',

                 'version'             => 'FAKE_VERSION',
            ),
            $options
        );

        return new FoursquareResourceOwner($this->buzzClient, $httpUtils, $options, $name);
    }
}
