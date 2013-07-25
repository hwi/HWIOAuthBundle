<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\OAuth\Response;

/**
 * VkontakteUserResponse
 *
 * @author Vladislav Vlastovskiy <me@vlastv.ru>
 */
class VkontakteUserResponse extends PathUserResponse
{
    /**
     * {@inheritdoc}
     */
    public function getRealName()
    {
        $lastName = $this->getValueForPath('last_name');
        $firstName = $this->getValueForPath('first_name');

        if ($firstName &&  $lastName) {
            return $firstName . ' ' . $lastName;
        } elseif ($firstName || $lastName) {
            return $firstName ?: $lastName;
        }

        return null;
    }
}
