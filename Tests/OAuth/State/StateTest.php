<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Tests\OAuth\State;

use HWI\Bundle\OAuthBundle\OAuth\Exception\StateRetrievalException;
use HWI\Bundle\OAuthBundle\OAuth\State\State;
use HWI\Bundle\OAuthBundle\Security\Helper\Nonce;
use Symfony\Component\Config\Definition\Exception\DuplicateKeyException;

final class StateTest extends \PHPUnit_Framework_TestCase
{
    const TEST_VALUES = [
        'foo' => 'bar',
        'bar' => 'baz',
        'foobar' => 'foobaz',
    ];

    public function testConstructorWithEncodedParameter()
    {
        $queryParameter = $this->encodeArray(self::TEST_VALUES);
        $state = new State($queryParameter);

        foreach (self::TEST_VALUES as $key => $value) {
            self::assertEquals($value, $state->get($key));
        }
    }

    public function testConstructorWithSingleValue()
    {
        $state = new State('random');
        self::assertEquals('random', $state->get());
    }

    public function testConstructorWithArrayParameter()
    {
        $state = new State(self::TEST_VALUES);

        foreach (self::TEST_VALUES as $key => $value) {
            self::assertEquals($value, $state->get($key));
        }
    }

    public function testFromEncodedParameterWithInvalidFormat()
    {
        self::expectException(\InvalidArgumentException::class);

        $values = ['some', 'indexed', 'array'];
        $queryParameter = $this->encodeArray($values);

        new State($queryParameter);
    }

    public function testGetNonExistentValue()
    {
        $state = new State($this->encodeArray(self::TEST_VALUES));
        self::expectException(StateRetrievalException::class);
        $state->get('baz');
    }

    public function testAdd()
    {
        $state = new State($this->encodeArray(self::TEST_VALUES));

        $state->add('baz', 'foo');
        self::assertEquals('foo', $state->get('baz'));
    }

    public function testAddDuplicateKey()
    {
        self::expectException(DuplicateKeyException::class);

        $state = new State($this->encodeArray(self::TEST_VALUES));
        $state->add('foo', 'foobar');
    }

    public function testEncode()
    {
        $expectedParameter = $this->encodeArray(self::TEST_VALUES);
        $state = new State($expectedParameter);

        self::assertEquals($expectedParameter, $state->encode());
    }

    public function testEncodeOnlyValue()
    {
        $state = new State('random');
        self::assertEquals('random', $state->encode());
    }

    public function testEncodeEmptyValue()
    {
        $state = new State(null);
        self::assertNull($state->encode());
    }

    public function testSetCsrfTokenSetsProvidedToken()
    {
        $token = Nonce::generate();
        $state = new State(null);

        $state->setCsrfToken($token);
        self::assertEquals($token, $state->getCsrfToken());
    }

    public function testSetCsrfTokenGeneratesToken()
    {
        $state = new State(null);
        self::assertNull($state->getCsrfToken());

        $state->setCsrfToken();
        self::assertNotNull($state->getCsrfToken());
    }

    /**
     * @param array $array The array to encode
     *
     * @return string The encoded result
     */
    private function encodeArray($array)
    {
        return urlencode(base64_encode(json_encode($array)));
    }
}
