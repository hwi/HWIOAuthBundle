<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\OAuth\State;

use HWI\Bundle\OAuthBundle\OAuth\Exception\StateRetrievalException;
use HWI\Bundle\OAuthBundle\OAuth\StateInterface;
use InvalidArgumentException;
use Symfony\Component\Config\Definition\Exception\DuplicateKeyException;

final class State implements StateInterface
{
    public const DEFAULT_KEY = 'state';
    public const CSRF_TOKEN_KEY = 'csrf_token';

    /**
     * @var array
     */
    private $values = [];

    /**
     * @param string|array<string,string>|null The state parameter as a string or assoc array
     *
     * @throws InvalidArgumentException
     */
    public function __construct($parameters)
    {
        if (!\is_array($parameters)) {
            $parameters = $this->parseStringParameter($parameters);
        }

        if (!$this->isAssociatedArray($parameters)) {
            throw new InvalidArgumentException('Constructor argument should be a non-empty, associative array');
        }

        foreach ($parameters as $key => $value) {
            $this->add($key, $value);
        }
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

    public function setCsrfToken(string $token): void
    {
        $this->values[self::CSRF_TOKEN_KEY] = $token;
    }

    public function getAll(): array
    {
        return $this->values;
    }

    public function getCsrfToken(): ?string
    {
        return $this->values[self::CSRF_TOKEN_KEY] ?? null;
    }

    /**
     * {@inheritdoc}
     *
     * Encodes the array of values to a string so it can be stored in a query parameter.
     * Returns the plain value if only the default key or CSRF token has been set.
     */
    public function encode(): string
    {
        if ($this->isOnlyExistentKey(self::DEFAULT_KEY)) {
            return urlencode($this->values[self::DEFAULT_KEY]);
        }

        if ($this->isOnlyExistentKey(self::CSRF_TOKEN_KEY)) {
            return urlencode($this->values[self::CSRF_TOKEN_KEY]);
        }

        return urlencode(self::encodeArray($this->values));
    }

    /**
     * @param string $queryParameter The state query parameter string
     *
     * @return array<string,string>
     */
    private function parseStringParameter(string $queryParameter = null): array
    {
        $urlDecoded = urldecode($queryParameter);
        $values = json_decode(base64_decode($urlDecoded), 1);

        if (null === $values) {
            $values[self::DEFAULT_KEY] = $urlDecoded;
        }

        return $values;
    }

    /**
     * @param array $array The array to encode
     *
     * @return string The encoded array
     */
    private static function encodeArray(array $array): string
    {
        return base64_encode(json_encode($array));
    }

    /**
     * Checks if a given key is set in the values array,
     * and if it's the only key present.
     *
     * @param string $key
     *
     * @return bool
     */
    private function isOnlyExistentKey(string $key): bool
    {
        return isset($this->values[$key]) && 1 === \count($this->values);
    }

    private function isAssociatedArray(?array $array): bool
    {
        if ([] === $array) {
            return false;
        }

        return array_keys($array) !== range(0, \count($array) - 1);
    }
}
