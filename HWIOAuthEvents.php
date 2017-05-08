<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle;

/**
 * @author Marek Štípek
 */
final class HWIOAuthEvents
{
    /**
     * @Event("HWI\Bundle\OAuthBundle\Event\GetResponseUserEvent")
     */
    const REGISTRATION_INITIALIZE = 'hwi_oauth.registration.initialize';

    /**
     * @Event("HWI\Bundle\OAuthBundle\Event\FormEvent")
     */
    const REGISTRATION_SUCCESS = 'hwi_oauth.registration.success';

    /**
     * @Event("HWI\Bundle\OAuthBundle\Event\GetResponseUserEvent")
     */
    const REGISTRATION_COMPLETED = 'hwi_oauth.registration.completed';

    /**
     * @Event("HWI\Bundle\OAuthBundle\Event\GetResponseUserEvent")
     */
    const CONNECT_INITIALIZE = 'hwi_oauth.connect.initialize';

    /**
     * @Event("HWI\Bundle\OAuthBundle\Event\GetResponseUserEvent")
     */
    const CONNECT_CONFIRMED = 'hwi_oauth.connect.confirmed';

    /**
     * @Event("HWI\Bundle\OAuthBundle\Event\FilterUserResponseEvent")
     */
    const CONNECT_COMPLETED = 'hwi_oauth.connect.completed';
}
