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

final class HWIOAuthEvents
{
    const RESOURCE_OWNER_INITIALIZE = 'hwi.resource_owner.initialize';
    const RESOURCE_OWNER_COMPLETE   = 'hwi.resource_owner.complete';

    const USER_CONNECT_INITIALIZE   = 'hwi.user_connect.initialize';
    const USER_CONNECT_VALIDATE     = 'hwi.user_connect.validate';
    const USER_CONNECT_COMPLETE     = 'hwi.user_connect.complete';
    const USER_CONNECT_CONFIRM      = 'hwi.user_connect.confirm';
    const USER_CONNECT_ERROR        = 'hwi.user_connect.error';
}
