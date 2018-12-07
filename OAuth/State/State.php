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
use HWI\Bundle\OAuthBundle\Security\Helper\Nonce;
use InvalidArgumentException;
use Symfony\Component\Config\Definition\Exception\DuplicateKeyException;

final class State implements StateInterface
{
    const DEFAULT_KEY = 'state';
    const CSRF_TOKEN_KEY = 'csrf_token';

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
     * @param string The state query parameter string
     *
     * @return array<string,string>
     */
    private function parseStringParameter($queryParameter)
    {
        $urlDecoded = urldecode($queryParameter);
        $values = json_decode(base64_decode($urlDecoded), 1);

        if (null === $values) {
            $values[self::DEFAULT_KEY] = $urlDecoded;
        }

        return $values;
    }

    /**
     * {@inheritdoc}
     */
    public function add($key, $value)
    {
        if (isset($this->values[$key])) {
            throw new DuplicateKeyException(sprintf('State key [%s] is already set.', $key));
        }

        $this->values[$key] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function get($key = self::DEFAULT_KEY)
    {
        if (!isset($this->values[$key])) {
            throw StateRetrievalException::forKey($key);
        }

        return $this->values[$key];
    }

    /**
     * @param string $token
     */
    public function setCsrfToken($token = null)
    {
        $this->values[self::CSRF_TOKEN_KEY] = $token;

        if (null === $token) {
            $this->values[self::CSRF_TOKEN_KEY] = Nonce::generate();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAll()
    {
        return $this->values;
    }

    /**
     * {@inheritdoc}
     */
    public function getCsrfToken()
    {
        return isset($this->values[self::CSRF_TOKEN_KEY]) ? $this->values[self::CSRF_TOKEN_KEY] : null;
    }

    /**
     * {@inheritdoc}
     *
     * Encodes the array of values to a string so it can be stored in a query parameter.
     * Returns the plain value if only the default key or CSRF token has been set.
     */
    public function encode()
    {
        if ($this->isOnlyExistentKey(self::DEFAULT_KEY)) {
            return urlencode($this->values[self::DEFAULT_KEY]);
        }

        if ($this->isOnlyExistentKey(self::CSRF_TOKEN_KEY)) {
            return urlencode($this->values[self::CSRF_TOKEN_KEY]);
        }

        return urlencode($this->encodeArray($this->values));
    }

    /**
     * @param array The array to encode
     *
     * @return string The encoded array
     */
    private static function encodeArray($array)
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
    private function isOnlyExistentKey($key)
    {
        return isset($this->values[$key]) && array_keys($this->values) === [$key];
    }

    /**
     * @param array
     *
     * @return bool
     */
    private function isAssociatedArray($array)
    {
        if ([] === $array) {
            return false;
        }

        return array_keys($array) !== range(0, \count($array) - 1);
    }
}
