<?php

namespace HWI\Bundle\OAuthBundle\OAuth;

/**
 * OAuthOptionsModifier.
 */
class OAuthOptionsModifier extends AbstractOptionsModifier
{
    /**
     * @param array $options
     * @param ResourceOwnerInterface $resourceOwner
     * @return array
     */
    public function modifyOptions(array $options, ResourceOwnerInterface $resourceOwner)
    {
        return $options;
    }
}
