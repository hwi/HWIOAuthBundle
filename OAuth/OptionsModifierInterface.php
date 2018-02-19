<?php

namespace HWI\Bundle\OAuthBundle\OAuth;

/**
 * OptionsModifierInterface.
 */
interface OptionsModifierInterface
{
    /**
     * modify options
     *
     * @param array $options
     * @param ResourceOwnerInterface $resourceOwner
     * @return mixed
     */
    public function modifyOptions(array $options, ResourceOwnerInterface $resourceOwner);
}
