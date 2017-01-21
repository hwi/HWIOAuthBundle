<?php

namespace HWI\Bundle\OAuthBundle\Tests\Controller;

use FOS\UserBundle\Form\Factory\FactoryInterface;
use HWI\Bundle\OAuthBundle\Form\RegistrationFormHandlerInterface;
use HWI\Bundle\OAuthBundle\Tests\Fixtures\User;
use Symfony\Component\Form\Form;

class ConnectControllerRegistrationActionTest extends AbstractConnectControllerTest
{
    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function testNotEnabled()
    {
        $this->container->setParameter('hwi_oauth.connect', false);

        $this->controller->registrationAction($this->request, time());
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @expectedExceptionMessage Cannot connect already registered account.
     */
    public function testAlreadyConnected()
    {
        $this->mockAuthorizationCheck();

        $this->controller->registrationAction($this->request, time());
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Cannot register an account.
     */
    public function testCannotRegisterBadError()
    {
        $key = time();

        $this->session->expects($this->once())
            ->method('get')
            ->with('_hwi_oauth.registration_error.'.$key)
            ->willReturn(new \Exception())
        ;

        $this->controller->registrationAction($this->request, $key);
    }

    public function testFailedProcess()
    {
        $key = time();

        $this->session->expects($this->once())
            ->method('get')
            ->with('_hwi_oauth.registration_error.'.$key)
            ->willReturn($this->createAccountNotLinkedException())
        ;

        $this->makeRegistrationForm();

        $registrationFormHandler = $this->getMockBuilder(RegistrationFormHandlerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $registrationFormHandler->expects($this->once())
            ->method('process')
            ->withAnyParameters()
            ->willReturn(false)
        ;
        $this->container->set('hwi_oauth.registration.form.handler', $registrationFormHandler);

        $this->templating->expects($this->once())
            ->method('renderResponse')
            ->with('HWIOAuthBundle:Connect:registration.html.twig')
        ;

        $this->controller->registrationAction($this->request, $key);
    }

    public function test()
    {
        $key = time();

        $this->session->expects($this->once())
            ->method('get')
            ->with('_hwi_oauth.registration_error.'.$key)
            ->willReturn($this->createAccountNotLinkedException())
        ;

        $this->makeRegistrationForm();

        $registrationFormHandler = $this->getMockBuilder(RegistrationFormHandlerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $registrationFormHandler->expects($this->once())
            ->method('process')
            ->withAnyParameters()
            ->willReturn(true)
        ;
        $this->container->set('hwi_oauth.registration.form.handler', $registrationFormHandler);

        $this->accountConnector->expects($this->once())
            ->method('connect')
        ;

        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
        ;

        $this->controller->registrationAction($this->request, $key);
    }

    private function makeRegistrationForm()
    {
        $registrationForm = $this->getMockBuilder(Form::class)
            ->disableOriginalConstructor()
            ->getMock();
        $registrationForm->expects($this->any())
            ->method('getData')
            ->willReturn(new User());

        $this->container->setParameter('hwi_oauth.fosub_enabled', true);

        if (interface_exists('FOS\UserBundle\Form\Factory\FactoryInterface')) {
            $registrationFormFactory = $this->getMockBuilder(FactoryInterface::class)
                ->disableOriginalConstructor()
                ->getMock();
            $registrationFormFactory->expects($this->any())
                ->method('createForm')
                ->willReturn($registrationForm);

            $this->container->set('hwi_oauth.registration.form.factory', $registrationFormFactory);
        } else {
            // FOSUser 1.3 BC. To be removed.
            $this->container->set('hwi_oauth.registration.form', $registrationForm);
        }
    }
}
