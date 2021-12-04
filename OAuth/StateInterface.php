<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\OAuth;

use HWI\Bundle\OAuthBundle\OAuth\Exception\StateRetrievalException;
use Symfony\Component\Config\Definition\Exception\DuplicateKeyException;

interface StateInterface
{
    public function __serialize(): array;

    public function __unserialize(array $data): void;

    /**
     * @param string $key   The key to store a value to
     * @param string $value The value to store
     *
     * @throws DuplicateKeyException
     */
    public function add(string $key, string $value);

    /**
     * @return string The value set to this key
     *
     * @throws StateRetrievalException
     */
    public function get(string $key): ?string;

    public function has(string $key): bool;

    /**
     * @return array<string, string>
     */
    public function getAll(): array;

    public function setCsrfToken(string $token): void;

    public function getCsrfToken(): ?string;

    public function encode(): ?string;
}
