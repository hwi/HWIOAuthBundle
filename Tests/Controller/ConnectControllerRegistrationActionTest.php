<?php

namespace HWI\Bundle\OAuthBundle\Tests\Controller;

use HWI\Bundle\OAuthBundle\Tests\Fixtures\User;

class ConnectConnectControllerRegistrationActionTest extends AbstractConnectControllerTest
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
        $this->getAuthorizationChecker()->expects($this->once())
            ->method('isGranted')
            ->with('IS_AUTHENTICATED_REMEMBERED')
            ->willReturn(true)
        ;

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

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Cannot register an account.
     */
    public function testCannotRegisterBadKey()
    {
        $key = time() - 500;

        $this->session->expects($this->once())
            ->method('get')
            ->with('_hwi_oauth.registration_error.'.$key)
            ->willReturn($this->createAccountNotLinkedException())
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

        $registrationFormHandler = $this->getMock('\HWI\Bundle\OAuthBundle\Form\RegistrationFormHandlerInterface');
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

        $registrationFormHandler = $this->getMock('\HWI\Bundle\OAuthBundle\Form\RegistrationFormHandlerInterface');
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
        $registrationForm = $this->getMockBuilder('\Symfony\Component\Form\Form')
            ->disableOriginalConstructor()->getMock();
        $registrationForm->expects($this->any())
            ->method('getData')
            ->willReturn(new User());
        $registrationFormFactory = $this->getMock('\FOS\UserBundle\Form\Factory\FactoryInterface');
        $registrationFormFactory->expects($this->any())
            ->method('createForm')
            ->willReturn($registrationForm)
        ;
        $this->container->set('hwi_oauth.registration.form.factory', $registrationFormFactory);
        // FOSUser 1.3 BC. To be removed.
        $this->container->set('hwi_oauth.registration.form', $registrationForm);

    }
}
