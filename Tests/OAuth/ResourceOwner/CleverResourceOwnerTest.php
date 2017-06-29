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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\CleverResourceOwner;

/**
 * CleverResourceOwnerTest.
 *
 * @author Matt Farmer <work@mattfarmer.net>
 */
class CleverResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected $resourceOwnerClass = CleverResourceOwner::class;

    public function setUp()
    {
        $this->resourceOwnerName = 'CleverResourceOwner';
        $this->resourceOwner = $this->createResourceOwner($this->resourceOwnerName);
    }
}
