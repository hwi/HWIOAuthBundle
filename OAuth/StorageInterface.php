<?php

namespace HWI\Bundle\OAuthBundle\OAuth;

/**
 * StorageInterface for tokens
 */
interface StorageInterface
{
    /**
     * @param ResourceOwnerInterface $resourceOwner
     *
     * @return array
     */
    function read(ResourceOwnerInterface $resourceOwner);

    /**
     * @param ResourceOwnerInterface $resourceOwner
     * @param array                  $token
     */
    function write(ResourceOwnerInterface $resourceOwner, $token);
}
