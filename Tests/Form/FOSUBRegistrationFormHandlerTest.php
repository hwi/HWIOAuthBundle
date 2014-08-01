<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Tests\Form;


use HWI\Bundle\OAuthBundle\Tests\Fixtures\FOSUser as User;
use HWI\Bundle\OAuthBundle\OAuth\Response\PathUserResponse;
use HWI\Bundle\OAuthBundle\Form\FOSUBRegistrationFormHandler;

class FOSUBRegistrationFormHandlerTest extends \PHPUnit_Framework_TestCase
{
    
    protected $resp;
    
    protected $handler;
    
    protected $facebookPaths = array(
        'identifier' => 'id',
        'username' => 'username',
    //'nickname'   => 'username',
        'realname'   => 'name',
        'email'      => 'email',
    );
    
    protected function setUp()
    {
        if (!interface_exists('FOS\UserBundle\Model\UserManagerInterface')) {
            $this->markTestSkipped('FOSUserBundle is not available');
        }
        $this->resp = array(
            'id' => '723491248',   
            'name' => 'Max Mustermann',
            'first_name' => 'Max',
            'last_name' => 'Mustermann',
            'link' => 'https://www.facebook.com/maxmuster',
            'gender' => 'male',
            'email' => 'max@muster.de',
            'timezone' => 1,
            'locale' => 'en_US',
            'verified' => true,
            'updated_time' => '2013-11-09T17:18:53+0000',
            'username' => 'maxmuster',
        );
        
        $this->handler = new FOSUBRegistrationFormHandler($this->createUserManagerMock(),$this->createMailerMock());

    }

    public function testSetUserInformationWithDefaultPaths(){
        
        $responseObj = new PathUserResponse();
        $responseObj->setPaths($this->facebookPaths);
        $responseObj->setResponse($this->resp);
        $user = new User();
        
        $class = new \ReflectionClass($this->handler);
        $method = $class->getMethod('setUserInformation');
        $method->setAccessible(true);
        $args = array($user, $responseObj);
        
        $method->invokeArgs($this->handler, $args);
        
        $this->assertEquals('maxmuster', $user->getUsername());
        $this->assertEquals('max@muster.de', $user->getEmail());
    }
    
    protected function createUserManagerMock(){
        $userManagerMock = $this->getMockBuilder('FOS\UserBundle\Model\UserManagerInterface')
            ->getMock();  
        return $userManagerMock;
    }
    
    protected function createMailerMock(){
        $mailerMock = $this->getMockBuilder('FOS\UserBundle\Mailer\MailerInterface')
                        ->getMock();
        return $mailerMock;
    }
}
