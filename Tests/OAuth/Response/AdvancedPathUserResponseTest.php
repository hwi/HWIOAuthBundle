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

use HWI\Bundle\OAuthBundle\OAuth\Response\AdvancedPathUserResponse;

class AdvancedPathUserResponseTest extends PathUserResponseTest
{
    public function setup()
    {
        $this->responseObject = new AdvancedPathUserResponse;
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
        // easy path
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
        // easy path
        $paths = array('profilepicture' => 'picture');
        $this->responseObject->setPaths($paths);
        $this->responseObject->setResponse(json_encode(array('no_picture' => 'foo@bar.baz')));

        $this->assertNull($this->responseObject->getProfilePicture());
    }
}
