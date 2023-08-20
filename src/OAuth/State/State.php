<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\OAuth\State;

use HWI\Bundle\OAuthBundle\OAuth\Exception\StateRetrievalException;
use HWI\Bundle\OAuthBundle\OAuth\StateInterface;
use Symfony\Component\Config\Definition\Exception\DuplicateKeyException;

final class State implements StateInterface
{
    public const DEFAULT_KEY = 'state';
    public const CSRF_TOKEN_KEY = 'csrf_token';

    /**
     * @var array<string, string>
     */
    private array $values = [];

    /**
     * @param string|array<string,string>|null $parameters The state parameter as a string or assoc array
     * @param bool                             $keepCsrf   Whether to keep the CSRF token in the state or not
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($parameters, bool $keepCsrf = true)
    {
        if (!\is_array($parameters)) {
            $parameters = $this->parseStringParameter($parameters);
        }

        if (null !== $parameters) {
            if (!$this->isAssociatedArray($parameters)) {
                throw new \InvalidArgumentException('Constructor argument should be a non-empty, associative array');
            }

            foreach ($parameters as $key => $value) {
                if (false === $keepCsrf && self::CSRF_TOKEN_KEY === $key) {
                    continue;
                }
                $this->add($key, $value);
            }
        }
    }

    public function __serialize(): array
    {
        return [
            'values' => $this->values,
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->values = $data['values'];
    }

    /**
     * {@inheritdoc}
     */
    public function add(string $key, string $value): void
    {
        if (isset($this->values[$key])) {
            throw new DuplicateKeyException(sprintf('State key [%s] is already set.', $key));
        }

        $this->values[$key] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $key): ?string
    {
        if (!isset($this->values[$key])) {
            throw StateRetrievalException::forKey($key);
        }

        return $this->values[$key];
    }

    public function has(string $key): bool
    {
        return \array_key_exists($key, $this->values);
    }

    public function setCsrfToken(string $token): void
    {
        $this->values[self::CSRF_TOKEN_KEY] = $token;
    }

    public function getAll(): array
    {
        $values = $this->values;
        unset($values[self::CSRF_TOKEN_KEY]);

        return $values;
    }

    public function getCsrfToken(): ?string
    {
        return $this->values[self::CSRF_TOKEN_KEY] ?? null;
    }

    /**
     * Encodes the array of values to a string so it can be stored in a query parameter.
     * Returns the plain value if only the default key or CSRF token has been set.
     */
    public function encode(): ?string
    {
        if (!$this->values) {
            return null;
        }

        $encoded = urlencode($this->encodeValues());

        return '' !== $encoded ? $encoded : null;
    }

    /**
     * @param string|null $queryParameter The state query parameter string
     *
     * @return array<string,string>|null
     */
    private function parseStringParameter(string $queryParameter = null): ?array
    {
        $urlDecoded = $queryParameter ? urldecode($queryParameter) : '';

        try {
            $values = json_decode(base64_decode($urlDecoded), true, 512, \JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            $values = null;
        }

        if (null === $values && '' !== $urlDecoded) {
            $values[self::DEFAULT_KEY] = $urlDecoded;
        }

        return $values;
    }

    /**
     * @return string The encoded array
     */
    private function encodeValues(): string
    {
        try {
            return base64_encode(json_encode($this->values, \JSON_THROW_ON_ERROR));
        } catch (\JsonException $e) {
            return '';
        }
    }

    private function isAssociatedArray(?array $array): bool
    {
        if ([] === $array) {
            return false;
        }

        return array_keys($array) !== range(0, \count($array) - 1);
    }
}
