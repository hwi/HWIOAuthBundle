<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Tests\OAuth\RequestDataStorage;

use HWI\Bundle\OAuthBundle\OAuth\RequestDataStorage\SessionStorage;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SessionStorageTest extends TestCase
{
    /**
     * @var MockObject|SessionInterface
     */
    private $session;

    /**
     * @var MockObject|ResourceOwnerInterface
     */
    private $resourceOwner;

    /**
     * @var SessionStorage
     */
    private $storage;

    protected function setUp(): void
    {
        $this->session = $this->createMock(SessionInterface::class);
        $this->resourceOwner = $this->createMock(ResourceOwnerInterface::class);
        $this->resourceOwner->method('getName')->willReturn('resource_owner_name');
        $this->resourceOwner->method('getOption')->with('client_id')->willReturn('client_id');

        $this->storage = new SessionStorage($this->session);
    }

    public function testSaveTokenWithoutOAuthTokenPassedThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid request token.');
        $this->storage->save($this->resourceOwner, [], 'token');
    }

    public function testSaveOAuthToken(): void
    {
        $key = '_hwi_oauth.resource_owner_name.client_id.token.oauth_token';
        $this->session
            ->expects(self::once())
            ->method('set')
            ->with($key, ['oauth_token' => 'oauth_token']);

        $this->storage->save($this->resourceOwner, ['oauth_token' => 'oauth_token'], 'token');
    }

    public function testSaveStringValue(): void
    {
        $key = '_hwi_oauth.resource_owner_name.client_id.csrf_state.csrf_token';
        $this->session
            ->expects(self::once())
            ->method('set')
            ->with($key, 'csrf_token');

        $this->storage->save($this->resourceOwner, 'csrf_token', 'csrf_state');
    }

    public function testSaveArrayValue(): void
    {
        $key = '_hwi_oauth.resource_owner_name.client_id.type.value';
        $this->session
            ->expects(self::once())
            ->method('set')
            ->with($key, ['value']);

        $this->storage->save($this->resourceOwner, ['value'], 'type');
    }

    public function testSaveObjectValue(): void
    {
        $class = new \stdClass();
        $key = '_hwi_oauth.resource_owner_name.client_id.type.stdClass';
        $this->session
            ->expects(self::once())
            ->method('set')
            ->with($key, serialize($class));

        $this->storage->save($this->resourceOwner, $class, 'type');
    }

    public function testFetchUnavailableKeyThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No data available in storage.');
        $this->storage->fetch($this->resourceOwner, 'not-existing-key', 'token');
    }

    public function testFetchTokenIsOneTimeUseOnly(): void
    {
        $key = '_hwi_oauth.resource_owner_name.client_id.token.oauth_token';
        $this->session
            ->expects(self::once())
            ->method('get')
            ->with($key)
            ->willReturn('oauth_token');
        $this->session
            ->expects(self::once())
            ->method('remove')
            ->with($key);

        $this->storage->fetch($this->resourceOwner, 'oauth_token', 'token');
    }

    public function testFetchOtherThenToken(): void
    {
        $class = new \stdClass();
        $key = '_hwi_oauth.resource_owner_name.client_id.state.stdClass';
        $this->session
            ->expects(self::once())
            ->method('get')
            ->with($key)
            ->willReturn(serialize($class));
        $this->session
            ->expects(self::never())
            ->method('remove')
            ->with($key);

        $data = $this->storage->fetch($this->resourceOwner, \get_class($class), 'state');
        self::assertEquals(serialize($class), $data);
    }
}
