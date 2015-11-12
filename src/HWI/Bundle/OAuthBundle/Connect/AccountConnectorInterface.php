<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Connect;

use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Account connector objects are responsible for connecting an OAuth response
 * to the appropriate fields of the user object.
 *
 * @author Alexander <iam.asm89@gmail.com>
 */
interface AccountConnectorInterface
{
    /**
     * Connects the response to the user object.
     *
     * @param UserInterface         $user     The user object
     * @param UserResponseInterface $response The oauth response
     */
    public function connect(UserInterface $user, UserResponseInterface $response);
}
