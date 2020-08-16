<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Security\Helper;

final class NonceGenerator
{
    private function __construct()
    {
    }

    public static function generate(): string
    {
        return md5(microtime(true).uniqid('', true));
    }
}
