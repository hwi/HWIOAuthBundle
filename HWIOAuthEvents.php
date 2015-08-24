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
 * Contains all events thrown in the HWIOAuthBundle
 * @author Maks Rafalko
 */
final class HWIOAuthEvents
{
    /**
     * The REGISTRATION_SUCCESS event occurs when the registration form is submitted successfully.
     *
     * This event allows you to set the response instead of using the default one.
     * The event listener method receives a HWI\Bundle\OAuthBundle\Event\FormEvent instance.
     */
    const REGISTRATION_SUCCESS = 'hwi_oauth.registration.success';
}
