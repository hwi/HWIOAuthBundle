<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Tests\Form;

use FOS\UserBundle\Mailer\MailerInterface;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use HWI\Bundle\OAuthBundle\Form\FOSUBRegistrationFormHandler;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

class FOSUBRegistrationFormHandlerTest extends TestCase
{
    protected function setUp(): void
    {
        if (!class_exists(\FOS\UserBundle\Model\User::class)) {
            $this->markTestSkipped('FOSUserBundle not installed.');
        }
    }

    public function testProcessReturnsFalseForNotPostRequest()
    {
        $formMock = $this->getForm(false);

        $response = $this->createMock(UserResponseInterface::class);

        $handler = new FOSUBRegistrationFormHandler($this->getUserManager(), $this->getMailer());

        $this->assertFalse($handler->process(Request::create('/'), $formMock, $response));
    }

    public function testProcessReturnsFalseForNotValidRequest()
    {
        $formMock = $this->getForm();
        $formMock
            ->expects($this->once())
            ->method('isValid')
            ->willReturn(false)
        ;

        $handler = new FOSUBRegistrationFormHandler($this->getUserManager(), $this->getMailer());

        $this->assertFalse($handler->process(Request::create('/', 'POST'), $formMock, $this->getResponse()));
    }

    public function testProcessReturnsTrueForValidRequest()
    {
        $formMock = $this->getForm();
        $formMock
            ->expects($this->once())
            ->method('isValid')
            ->willReturn(true)
        ;

        $handler = new FOSUBRegistrationFormHandler($this->getUserManager(), $this->getMailer());

        $this->assertTrue($handler->process(Request::create('/', 'POST'), $formMock, $this->getResponse()));
    }

    private function getUserManager()
    {
        $mock = $this->createMock(UserManagerInterface::class);

        $userMock = $this->createMock(UserInterface::class);
        $userMock
            ->expects($this->once())
            ->method('setEnabled')
            ->with(true)
        ;

        $mock
            ->expects($this->once())
            ->method('createUser')
            ->willReturn($userMock)
        ;

        return $mock;
    }

    private function getMailer()
    {
        return $this->createMock(MailerInterface::class);
    }

    private function getResponse()
    {
        return $this->createMock(UserResponseInterface::class);
    }

    private function getForm($handle = true)
    {
        $formMock = $this->createMock(Form::class);

        if ($handle) {
            $formMock
                ->expects($this->once())
                ->method('setData')
            ;
            $formMock
                ->expects($this->once())
                ->method('handleRequest')
            ;
        }

        return $formMock;
    }
}
