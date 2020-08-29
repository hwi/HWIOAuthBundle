<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Tests\OAuth\State;

use HWI\Bundle\OAuthBundle\OAuth\Exception\StateRetrievalException;
use HWI\Bundle\OAuthBundle\OAuth\State\State;
use HWI\Bundle\OAuthBundle\Security\Helper\NonceGenerator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\DuplicateKeyException;

final class StateTest extends TestCase
{
    public const TEST_VALUES = [
        'foo' => 'bar',
        'bar' => 'baz',
        'foobar' => 'foobaz',
    ];

    public function testConstructorWithEncodedParameter()
    {
        $state = new State($this->encodeArray(self::TEST_VALUES));

        foreach (self::TEST_VALUES as $key => $value) {
            self::assertEquals($value, $state->get($key));
        }
    }

    public function testConstructorWithNull()
    {
        $state = new State(null);
        self::assertCount(0, $state->getAll());
    }

    public function testConstructorWithSingleValue()
    {
        $state = new State('random');
        self::assertEquals('random', $state->get('state'));
    }

    public function testConstructorWithArrayParameter()
    {
        $state = new State(self::TEST_VALUES);

        foreach (self::TEST_VALUES as $key => $value) {
            self::assertEquals($value, $state->get($key));
        }
    }

    public function testConstructorWithArrayParameterWithoutKeepingCSRFToken()
    {
        $state = new State(array_merge(self::TEST_VALUES, ['csrf_token' => 'csrf']), false);

        foreach (self::TEST_VALUES as $key => $value) {
            self::assertEquals($value, $state->get($key));
        }
        self::assertArrayNotHasKey('csrf_token', $state->getAll());
    }

    public function testItCanBeSerializedAndUnserialized()
    {
        $state = new State(self::TEST_VALUES);
        $unserialized = unserialize(serialize($state));

        self::assertEquals($state, $unserialized);
    }

    public function testFromEncodedParameterWithInvalidFormat()
    {
        $this->expectException(\InvalidArgumentException::class);

        $values = ['some', 'indexed', 'array'];

        new State($this->encodeArray($values));
    }

    public function testGetNonExistentValue()
    {
        $this->expectException(StateRetrievalException::class);

        $state = new State($this->encodeArray(self::TEST_VALUES));
        $state->get('baz');
    }

    public function testAdd()
    {
        $state = new State($this->encodeArray(self::TEST_VALUES));

        $state->add('baz', 'foo');
        self::assertEquals('foo', $state->get('baz'));
    }

    public function testHas()
    {
        $state = new State($this->encodeArray(self::TEST_VALUES));
        self::assertTrue($state->has('foo'));
    }

    public function testAddDuplicateKey()
    {
        $this->expectException(DuplicateKeyException::class);

        $state = new State($this->encodeArray(self::TEST_VALUES));
        $state->add('foo', 'foobar');
    }

    public function testEncode()
    {
        $expectedParameter = $this->encodeArray(self::TEST_VALUES);
        $state = new State($expectedParameter);

        self::assertEquals($expectedParameter, $state->encode());
    }

    public function testEncodeWithEmptyState()
    {
        $state = new State('');

        self::assertNull($state->encode());
    }

    public function testEncodeEmptyValue()
    {
        $state = new State(null);
        self::assertEmpty($state->encode());

        $state = new State('');
        self::assertEmpty($state->encode());
    }

    public function testSetCsrfTokenSetsProvidedToken()
    {
        $token = NonceGenerator::generate();

        $state = new State(null);
        self::assertNull($state->getCsrfToken());

        $state->setCsrfToken($token);
        self::assertEquals($token, $state->getCsrfToken());
    }

    public function testGetAllKeepingCSRFToken()
    {
        $state = new State(array_merge(self::TEST_VALUES, ['csrf_token' => 'csrf']), false);
        self::assertArrayNotHasKey('csrf_token', $state->getAll());
    }

    private function encodeArray(array $array): string
    {
        return urlencode(base64_encode(json_encode($array)));
    }
}
