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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\ExactOnlineResourceOwner;

class ExactOnlineResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected $userResponse = <<<json
{
 "d" : {
  "results": [ {
    "__metadata": {
       "uri": "https://start.exactonline.nl/api/v1/current/Me(guid'cabb67b6-1d5b-45fe-tttt-fbce92e5e24b')",
       "type": "Exact.Web.Api.System.Me"
    },
    "UserID": "1",
    "FullName": "Test User",
    "PictureUrl": "https://start.exactonline.nl//docs/images/placeholder_contact_myeol.png",
    "UserName": "bar", 
    "Email": "example@example.com"
  }]
 }
}
json;

    protected $paths = array(
    	'identifier' => 'UserID',
    	'nickname' => 'UserName',
    	'realname' => 'FullName',
    	'email' => 'Email',
    	'profilepicture' => 'PictureUrl'
    );
    
    protected $expectedUrls = array(
        'authorization_url'      => 'http://user.auth/?test=2&response_type=code&client_id=clientid&scope=code&redirect_uri=http%3A%2F%2Fredirect.to%2F',
        'authorization_url_csrf' => 'http://user.auth/?test=2&response_type=code&client_id=clientid&scope=code&state=random&redirect_uri=http%3A%2F%2Fredirect.to%2F',
    );

    protected function setUpResourceOwner($name, $httpUtils, array $options)
    {
        return new ExactOnlineResourceOwner($this->buzzClient, $httpUtils, $options, $name, $this->storage);
    }
}
