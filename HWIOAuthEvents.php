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

final class HWIOAuthEvents {
    /**
     * Fired after the account of an authenticated user was connected with a remote service
     */
    const HWIOAUTH_CONNECTED = 'hwioauth.connected';
}