<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Tests\OAuth\Response;

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\PathUserResponse;
use PHPUnit\Framework\TestCase;

class PathUserResponseTest extends TestCase
{
    /**
     * @var PathUserResponse
     */
    private $responseObject;

    protected function setUp(): void
    {
        $this->responseObject = new PathUserResponse();
    }

    public function testGetSetResponseDataWithJsonString()
    {
        $response = ['foo' => 'bar'];

        $this->responseObject->setData(json_encode($response));
        $this->assertEquals($response, $this->responseObject->getData());
    }

    public function testGetSetResponseDataWithPhpArray()
    {
        $response = ['foo' => 'bar'];

        $this->responseObject->setData($response);
        $this->assertEquals($response, $this->responseObject->getData());
    }

    public function testSetInvalidResponseData()
    {
        $this->expectException(\Symfony\Component\Security\Core\Exception\AuthenticationException::class);

        $this->responseObject->setData('not_json');
    }

    public function testGetSetResourceOwner()
    {
        $resourceOwner = $this->createMock(ResourceOwnerInterface::class);

        $this->responseObject->setResourceOwner($resourceOwner);
        $this->assertEquals($resourceOwner, $this->responseObject->getResourceOwner());
    }

    public function testGetPathsReturnsDefaultDefinedPaths()
    {
        $paths = [
            'identifier' => null,
            'nickname' => null,
            'firstname' => null,
            'lastname' => null,
            'realname' => null,
            'email' => null,
            'profilepicture' => null,
        ];

        $this->assertEquals($paths, $this->responseObject->getPaths());
    }

    public function testSetPathsAddsNewPathsToAlreadyDefined()
    {
        $paths = [
            'identifier' => null,
            'nickname' => null,
            'firstname' => null,
            'lastname' => null,
            'realname' => null,
            'email' => null,
            'profilepicture' => null,
            'foo' => 'bar',
        ];

        $responseObject = new PathUserResponse();
        $responseObject->setPaths(['foo' => 'bar']);
        $this->assertEquals($paths, $responseObject->getPaths());
    }

    public function testGetUsername()
    {
        $paths = ['identifier' => 'id'];

        $this->responseObject->setPaths($paths);
        $this->responseObject->setData(json_encode(['id' => 666]));

        $this->assertEquals(666, $this->responseObject->getUsername());
    }

    public function testGetUsernameWithoutResponseReturnsNull()
    {
        $this->responseObject->setPaths(['identifier' => 'id']);
        $this->assertNull($this->responseObject->getUsername());
    }

    public function testGetNickname()
    {
        // easy path
        $paths = ['nickname' => 'foo'];

        $this->responseObject->setPaths($paths);
        $this->responseObject->setData(json_encode(['foo' => 'bar']));

        $this->assertEquals('bar', $this->responseObject->getNickname());

        // nesting
        $paths = ['nickname' => 'foo.bar'];

        $this->responseObject->setPaths($paths);
        $this->responseObject->setData(json_encode(['foo' => ['bar' => 'qux']]));

        $this->assertEquals('qux', $this->responseObject->getNickname());
    }

    public function testGetRealName()
    {
        $paths = ['realname' => 'foo'];

        $this->responseObject->setPaths($paths);
        $this->responseObject->setData(json_encode(['foo' => 'bar']));

        $this->assertEquals('bar', $this->responseObject->getRealName());
    }

    public function testGetIdentifierInvalidPathReturnsNull()
    {
        $paths = ['identifier' => 'non_existing'];

        $this->responseObject->setPaths($paths);
        $this->responseObject->setData(json_encode(['foo' => 'bar']));

        $this->assertNull($this->responseObject->getNickname());
    }

    public function testNoIdentifierPathReturnsNull()
    {
        $paths = ['non_username' => 'non_existing'];

        $responseObject = new PathUserResponse();
        $responseObject->setPaths($paths);
        $responseObject->setData(json_encode(['foo' => 'bar']));

        $this->assertNull($responseObject->getNickname());
    }

    public function testGetEmail()
    {
        $paths = ['email' => 'email'];

        $this->responseObject->setPaths($paths);
        $this->responseObject->setData(json_encode(['email' => 'foo@bar.baz']));

        $this->assertEquals('foo@bar.baz', $this->responseObject->getEmail());
    }

    public function testGetEmailNotInResponse()
    {
        $paths = ['email' => 'email'];

        $this->responseObject->setPaths($paths);
        $this->responseObject->setData(json_encode(['no_email' => 'foo@bar.baz']));

        $this->assertNull($this->responseObject->getEmail());
    }

    public function testGetProfilePicture()
    {
        $paths = ['profilepicture' => 'picture'];

        $this->responseObject->setPaths($paths);
        $this->responseObject->setData(json_encode(['picture' => 'http://img']));

        $this->assertEquals('http://img', $this->responseObject->getProfilePicture());
    }

    public function testGetProfilePictureNotInResponse()
    {
        $paths = ['profilepicture' => 'picture'];

        $this->responseObject->setPaths($paths);
        $this->responseObject->setData(json_encode(['no_picture' => 'foo@bar.baz']));

        $this->assertNull($this->responseObject->getProfilePicture());
    }

    public function testGetMergeOfPathsIntoSingleField()
    {
        $paths = ['realname' => ['first_name', 'last_name']];

        $this->responseObject->setPaths($paths);
        $this->responseObject->setData(['first_name' => 'foo', 'last_name' => 'bar']);

        $this->assertEquals('foo bar', $this->responseObject->getRealName());

        $this->responseObject->setData(['first_name' => null, 'last_name' => 'bar']);

        $this->assertEquals('bar', $this->responseObject->getRealName());
    }
}
