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

use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * AdvancedPathUserResponse
 *
 * @author Geoffrey Bachelet <geoffrey.bachelet@gmail.com>
 * @author Alexander <iam.asm89@gmail.com>
 */
class AdvancedPathUserResponse extends PathUserResponse
    implements AdvancedUserResponseInterface
{
    /**
     * {@inheritdoc}
     */
    public function getEmail()
    {
        return $this->getValueForPath('email', false);
    }

    /**
     * {@inheritdoc}
     */
    public function getProfilePicture()
    {
        return $this->getValueForPath('profilepicture', false);
    }
}
