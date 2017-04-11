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
    const REGISTRATION_INITIALIZE = 'hwi_oauth.registration.initialize';

    const REGISTRATION_SUCCESS = 'hwi_oauth.registration.success';

    const REGISTRATION_COMPLETED = 'hwi_oauth.registration.completed';

    const CONNECT_INITIALIZE = 'hwi_oauth.connect.initialize';

    const CONNECT_CONFIRMED = 'hwi_oauth.connect.confirmed';

    const CONNECT_COMPLETED = 'hwi_oauth.connect.completed';
}
