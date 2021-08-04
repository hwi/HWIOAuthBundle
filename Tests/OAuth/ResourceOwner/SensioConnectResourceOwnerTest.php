<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Tests\OAuth\ResourceOwner;

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\SensioConnectResourceOwner;
use HWI\Bundle\OAuthBundle\OAuth\Response\SensioConnectUserResponse;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class SensioConnectResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected string $resourceOwnerClass = SensioConnectResourceOwner::class;

    protected bool $csrf = true;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userResponse = file_get_contents(__DIR__.'/../../Fixtures/sensioconnect_response.xml');
    }

    public function testGetUserInformation()
    {
        $class = SensioConnectUserResponse::class;
        $resourceOwner = $this->createResourceOwner(
            ['user_response_class' => $class],
            [],
            [
                $this->createMockResponse($this->userResponse, 'application/xml'),
            ]
        );

        /**
         * @var SensioConnectUserResponse
         */
        $userResponse = $resourceOwner->getUserInformation(['access_token' => 'token']);

        $this->assertInstanceOf($class, $userResponse);
        $this->assertEquals('aa5e22b0-6189-4113-9c68-91d4a3c32b7c', $userResponse->getUsername());
        $this->assertEquals('bar', $userResponse->getNickname());
        $this->assertEquals('Fake Guy', $userResponse->getRealName());
        $this->assertEquals('fake@email.com', $userResponse->getEmail());
        $this->assertEquals('token', $userResponse->getAccessToken());
    }

    public function testCustomResponseClass()
    {
        $this->markTestSkipped('SensioConnect already tests this approach');
    }

    public function testGetUserInformationFailure()
    {
        $this->expectException(AuthenticationException::class);

        $resourceOwner = $this->createResourceOwner(
            [],
            [],
            [
                $this->createMockResponse('invalid', 'application/xml'),
            ]
        );
        $resourceOwner->getUserInformation(['access_token' => 'token']);
    }
}
