<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Tests\OAuth\Response;

use HWI\Bundle\OAuthBundle\OAuth\Response\PathUserResponse;

class PathUserResponseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PathUserResponse
     */
    private $responseObject;

    public function setUp()
    {
        $this->responseObject = new PathUserResponse();
    }

    public function testGetSetResponseWithJsonString()
    {
        $response = array('foo' => 'bar');

        $this->responseObject->setResponse(json_encode($response));
        $this->assertEquals($response, $this->responseObject->getResponse());
    }

    public function testGetSetResponseWithPhpArray()
    {
        $response = array('foo' => 'bar');

        $this->responseObject->setResponse($response);
        $this->assertEquals($response, $this->responseObject->getResponse());
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AuthenticationException
     */
    public function testSetInvalidResponse()
    {
        $this->responseObject->setResponse('not_json');
    }

    public function testGetSetResourceOwner()
    {
        $resourceOwner = $this->getMockBuilder('\HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->responseObject->setResourceOwner($resourceOwner);
        $this->assertEquals($resourceOwner, $this->responseObject->getResourceOwner());
    }

    public function testGetPathsReturnsDefaultDefinedPaths()
    {
        $paths = array(
            'identifier'     => null,
            'nickname'       => null,
            'firstname'      => null,
            'lastname'       => null,
            'realname'       => null,
            'email'          => null,
            'profilepicture' => null,
        );

        $this->assertEquals($paths, $this->responseObject->getPaths());
    }

    public function testSetPathsAddsNewPathsToAlreadyDefined()
    {
        $paths = array(
            'identifier'     => null,
            'nickname'       => null,
            'firstname'      => null,
            'lastname'       => null,
            'realname'       => null,
            'email'          => null,
            'profilepicture' => null,
            'foo'            => 'bar'
        );

        $responseObject = new PathUserResponse();
        $responseObject->setPaths(array('foo' => 'bar'));
        $this->assertEquals($paths, $responseObject->getPaths());
    }

    public function testGetUsername()
    {
        $paths = array('identifier' => 'id');

        $this->responseObject->setPaths($paths);
        $this->responseObject->setResponse(json_encode(array('id' => 666)));

        $this->assertEquals(666, $this->responseObject->getUsername());
    }

    public function testGetUsernameWithoutResponseReturnsNull()
    {
        $this->responseObject->setPaths(array('identifier' => 'id'));
        $this->assertNull($this->responseObject->getUsername());
    }

    public function testGetNickname()
    {
        // easy path
        $paths = array('nickname' => 'foo');

        $this->responseObject->setPaths($paths);
        $this->responseObject->setResponse(json_encode(array('foo' => 'bar')));

        $this->assertEquals('bar', $this->responseObject->getNickname());

        // nesting
        $paths = array('nickname' => 'foo.bar');

        $this->responseObject->setPaths($paths);
        $this->responseObject->setResponse(json_encode(array('foo' => array('bar' => 'qux'))));

        $this->assertEquals('qux', $this->responseObject->getNickname());
    }

    public function testGetRealName()
    {
        $paths = array('realname' => 'foo');

        $this->responseObject->setPaths($paths);
        $this->responseObject->setResponse(json_encode(array('foo' => 'bar')));

        $this->assertEquals('bar', $this->responseObject->getRealName());
    }

    public function testGetIdentifierInvalidPathReturnsNull()
    {
        $paths = array('identifier' => 'non_existing');

        $this->responseObject->setPaths($paths);
        $this->responseObject->setResponse(json_encode(array('foo' => 'bar')));

        $this->assertNull($this->responseObject->getNickname());
    }

    public function testNoIdentifierPathReturnsNull()
    {
        $paths = array('non_username' => 'non_existing');

        $responseObject = new PathUserResponse();
        $responseObject->setPaths($paths);
        $responseObject->setResponse(json_encode(array('foo' => 'bar')));

        $this->assertNull($responseObject->getNickname());
    }

    public function testGetEmail()
    {
        $paths = array('email' => 'email');

        $this->responseObject->setPaths($paths);
        $this->responseObject->setResponse(json_encode(array('email' => 'foo@bar.baz')));

        $this->assertEquals('foo@bar.baz', $this->responseObject->getEmail());
    }

    public function testGetEmailNotInResponse()
    {
        $paths = array('email' => 'email');

        $this->responseObject->setPaths($paths);
        $this->responseObject->setResponse(json_encode(array('no_email' => 'foo@bar.baz')));

        $this->assertNull($this->responseObject->getEmail());
    }

    public function testGetProfilePicture()
    {
        $paths = array('profilepicture' => 'picture');

        $this->responseObject->setPaths($paths);
        $this->responseObject->setResponse(json_encode(array('picture' => 'http://img')));

        $this->assertEquals('http://img', $this->responseObject->getProfilePicture());
    }

    public function testGetProfilePictureNotInResponse()
    {
        $paths = array('profilepicture' => 'picture');

        $this->responseObject->setPaths($paths);
        $this->responseObject->setResponse(json_encode(array('no_picture' => 'foo@bar.baz')));

        $this->assertNull($this->responseObject->getProfilePicture());
    }

    public function testGetMergeOfPathsIntoSingleField()
    {
        $paths = array('realname' => array('first_name', 'last_name'));

        $this->responseObject->setPaths($paths);
        $this->responseObject->setResponse(array('first_name' => 'foo', 'last_name' => 'bar'));

        $this->assertEquals('foo bar', $this->responseObject->getRealName());

        $this->responseObject->setResponse(array('first_name' => null, 'last_name' => 'bar'));

        $this->assertEquals('bar', $this->responseObject->getRealName());
    }
}
