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

class PathUserResponseTest extends \PHPUnit_Framework_Testcase
{
    protected $responseObject;

    public function setup()
    {
        $this->responseObject = new PathUserResponse;
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
        $this->assertEquals($response, $this->responseObject->getResponse());
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
        $paths = array('username' => 'foo');
        $this->responseObject->setPaths($paths);
        $this->responseObject->setResponse(json_encode(array('foo' => 'bar')));

        $this->assertEquals('bar', $this->responseObject->getUsername());

        // nesting
        $paths = array('username' => 'foo.bar');
        $this->responseObject->setPaths($paths);
        $this->responseObject->setResponse(json_encode(array('foo' => array('bar' => 'qux'))));

        $this->assertEquals('qux', $this->responseObject->getUsername());
    }

    public function testGetDisplayName()
    {
        // easy path
        $paths = array('displayname' => 'foo');
        $this->responseObject->setPaths($paths);
        $this->responseObject->setResponse(json_encode(array('foo' => 'bar')));

        $this->assertEquals('bar', $this->responseObject->getDisplayName());
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AuthenticationException
     */
    public function testGetUsernameInvalidPath()
    {
        // easy path
        $paths = array('username' => 'non_existing');
        $this->responseObject->setPaths($paths);
        $this->responseObject->setResponse(json_encode(array('foo' => 'bar')));

        // should throw exception
        $this->responseObject->getUsername();
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AuthenticationException
     */
    public function testNoUsernamePath()
    {
        // easy path
        $paths = array('non_username' => 'non_existing');
        $this->responseObject->setPaths($paths);
        $this->responseObject->setResponse(json_encode(array('foo' => 'bar')));

        // should throw exception
        $this->responseObject->getUsername();
    }
}
