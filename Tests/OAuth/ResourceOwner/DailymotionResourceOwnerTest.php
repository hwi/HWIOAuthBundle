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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\DailymotionResourceOwner;

class DailymotionResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected $resourceOwnerClass = DailymotionResourceOwner::class;
    protected $userResponse = <<<json
{
    "id": "1",
    "screenname": "bar"
}
json;

    protected $paths = array(
        'identifier' => 'id',
        'nickname' => 'screenname',
        'realname' => 'fullname',
    );

    public function testDisplayPopup()
    {
        $resourceOwner = $this->createResourceOwner($this->resourceOwnerName, array('display' => 'popup'));

        $this->assertEquals(
            $this->options['authorization_url'].'&response_type=code&client_id=clientid&state=random&redirect_uri=http%3A%2F%2Fredirect.to%2F&display=popup',
            $resourceOwner->getAuthorizationUrl('http://redirect.to/')
        );
    }

    /**
     * @expectedException \Symfony\Component\OptionsResolver\Exception\ExceptionInterface
     */
    public function testInvalidDisplayOptionValueThrowsException()
    {
        $this->createResourceOwner($this->resourceOwnerName, array('display' => 'invalid'));
    }
}
