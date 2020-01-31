<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Contracts\EventDispatcher\Event as ContractsEvent;

if (class_exists(Event::class)) {
    // Symfony < 5
    abstract class AbstractEvent extends Event
    {
    }
} else {
    // Symfony 5
    abstract class AbstractEvent extends ContractsEvent
    {
    }
}
