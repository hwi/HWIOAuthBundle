<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\ErrorHandler\ErrorHandler;

if (file_exists($file = __DIR__.'/../vendor/autoload.php')) {
    $autoload = require_once $file;
} else {
    throw new RuntimeException('Install dependencies using Composer, to be able to run test suite.');
}

set_exception_handler([new ErrorHandler(), 'handleException']);

return $autoload;
