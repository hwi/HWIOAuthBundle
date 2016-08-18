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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\Office365ResourceOwner;

class Office365ResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    /**
     * {@inheritdoc}
     */
    protected function setUpResourceOwner($name, $httpUtils, array $options)
    {
        return new Office365ResourceOwner($this->buzzClient, $httpUtils, $options, $name, $this->storage);
    }
}
