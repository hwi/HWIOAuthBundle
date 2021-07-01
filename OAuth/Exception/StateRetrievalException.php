<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\OAuth\Exception;

final class StateRetrievalException extends \InvalidArgumentException
{
    /**
     * @param string $key The provided string key
     */
    public static function forKey(string $key): self
    {
        return new static(sprintf('No value found in state for key [%s]', $key));
    }
}
