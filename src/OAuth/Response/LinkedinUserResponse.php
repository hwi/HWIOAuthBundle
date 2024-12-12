<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\OAuth\Response;

final class LinkedinUserResponse extends PathUserResponse
{
    /**
     * {@inheritdoc}
     */
    public function getFirstName(): ?string
    {
        return $this->getValueForPath('firstname');
    }

    /**
     * {@inheritdoc}
     */
    public function getLastName(): ?string
    {
        return $this->getValueForPath('lastname');
    }

    /**
     * {@inheritdoc}
     */
    public function getProfilePicture(): ?string
    {
        return $this->getValueForPath('profilepicture');
    }

}
