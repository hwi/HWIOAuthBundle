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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\GamewispResourceOwner;
use Symfony\Component\HttpFoundation\Request;

/**
 * GamewispResourceOwnerTest
 *
 * @author John Madrak <john@madrak.net>
 */
class GamewispResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    public function setUp()
    {
        $this->resourceOwnerName = 'GamewispResourceOwner';
        $this->resourceOwner     = $this->createResourceOwner($this->resourceOwnerName);
    }

    protected function setUpResourceOwner($name, $httpUtils, array $options)
    {
        return new GamewispResourceOwner($this->buzzClient, $httpUtils, $options, $name, $this->storage);
    }
}
