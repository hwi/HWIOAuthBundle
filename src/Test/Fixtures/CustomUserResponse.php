<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Test\Fixtures;

use HWI\Bundle\OAuthBundle\OAuth\Response\AbstractUserResponse;

/**
 * @author Alexander <iam.asm89@gmail.com>
 */
final class CustomUserResponse extends AbstractUserResponse
{
    public function getUserIdentifier(): string
    {
        return 'foo666';
    }

    public function getUsername(): string
    {
        return 'foo666';
    }

    public function getNickname(): string
    {
        return 'foo';
    }

    public function getFirstName(): string
    {
        return 'foo';
    }

    public function getLastName(): string
    {
        return 'BAR';
    }

    public function getRealName(): string
    {
        return 'foo';
    }
}
