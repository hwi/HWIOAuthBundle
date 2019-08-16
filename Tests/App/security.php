<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Controller\UserValueResolver;

if (method_exists(Security::class, 'getUser') && !class_exists(UserValueResolver::class)) {
    $container->loadFromExtension('security', [
        'firewalls' => [
            'login_area' => [
                'logout_on_user_change' => true,
            ],
            'secured_area' => [
                'logout_on_user_change' => true,
            ],
        ],
    ]);
}
