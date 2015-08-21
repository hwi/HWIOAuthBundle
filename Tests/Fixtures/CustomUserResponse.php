<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Tests\Fixtures;

use HWI\Bundle\OAuthBundle\OAuth\Response\AbstractUserResponse;

/**
 * CustomUserResponse.
 *
 * @author Alexander <iam.asm89@gmail.com>
 */
class CustomUserResponse extends AbstractUserResponse
{
    public function getUsername()
    {
        return 'foo666';
    }

    public function getNickname()
    {
        return 'foo';
    }

    public function getFirstName()
    {
        return 'foo';
    }

    public function getLastName()
    {
        return 'BAR';
    }

    public function getRealName()
    {
        return 'foo';
    }
}
