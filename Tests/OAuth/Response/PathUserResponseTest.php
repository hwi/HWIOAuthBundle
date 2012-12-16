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
    protected $responseObject;

    public function setUp()
    {
        $this->responseObject = new PathUserResponse();
    }

    public function testGetSetResponse()
    {
        $response = array('foo' => 'bar');

        $this->responseObject->setResponse(json_encode($response));
        $this->assertEquals($response, $this->responseObject->getResponse());
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AuthenticationException
     */
    public function testSetInvalidResponse()
    {
        $this->responseObject->setResponse('not_json');

        // should throw exception
        $this->responseObject->getResponse();
    }

    public function testGetSetResourceOwner()
    {
        $resourceOwner = $this->getMockBuilder('\HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface')
            ->disableOriginalConstructor()->getMock();

        $this->responseObject->setResourceOwner($resourceOwner);
        $this->assertEquals($resourceOwner, $this->responseObject->getResourceOwner());

    }

    public function testGetSetPaths()
    {
        $paths = array('foo' => 'bar');
        $this->responseObject->setPaths($paths);
        $this->assertEquals($paths, $this->responseObject->getPaths());
    }

    public function testGetUsername()
    {
        // easy path
        $paths = array('identifier' => 'id');
        $this->responseObject->setPaths($paths);
        $this->responseObject->setResponse(json_encode(array('id' => 666)));

        $this->assertEquals(666, $this->responseObject->getUsername());
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
        // easy path
        $paths = array('realname' => 'foo');
        $this->responseObject->setPaths($paths);
        $this->responseObject->setResponse(json_encode(array('foo' => 'bar')));

        $this->assertEquals('bar', $this->responseObject->getRealName());
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AuthenticationException
     */
    public function testGetIdentifierInvalidPath()
    {
        // easy path
        $paths = array('identifier' => 'non_existing');
        $this->responseObject->setPaths($paths);
        $this->responseObject->setResponse(json_encode(array('foo' => 'bar')));

        // should throw exception
        $this->responseObject->getNickname();
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AuthenticationException
     */
    public function testNoIdentifierPath()
    {
        // easy path
        $paths = array('non_username' => 'non_existing');
        $this->responseObject->setPaths($paths);
        $this->responseObject->setResponse(json_encode(array('foo' => 'bar')));

        // should throw exception
        $this->responseObject->getNickname();
    }
}
