<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Tests\OAuth\ResourceOwner;

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\CleverResourceOwner;
use HWI\Bundle\OAuthBundle\Test\OAuth\ResourceOwner\GenericOAuth2ResourceOwnerTestCase;

/**
 * CleverResourceOwnerTest.
 *
 * @author Matt Farmer <work@mattfarmer.net>
 */
final class CleverResourceOwnerTest extends GenericOAuth2ResourceOwnerTestCase
{
    protected $resourceOwnerClass = CleverResourceOwner::class;

    protected function setUp(): void
    {
        $this->resourceOwnerName = 'CleverResourceOwner';
        $this->resourceOwner = $this->createResourceOwner($this->resourceOwnerName);
    }
}
