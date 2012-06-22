<?php

namespace HWI\Bundle\OAuthBundle\OAuth;

/**
 * StorageInterface for tokens
 */
interface StorageInterface
{
    /**
     * @param ResourceOwnerInterface $resourceOwner
     * @param string                 $tokenId
     *
     * @return array
     */
    function read(ResourceOwnerInterface $resourceOwner, $tokenId);

    /**
     * @param ResourceOwnerInterface $resourceOwner
     * @param array                  $token
     */
    function write(ResourceOwnerInterface $resourceOwner, $token);
}
