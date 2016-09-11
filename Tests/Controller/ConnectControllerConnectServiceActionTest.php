<?php

namespace HWI\Bundle\OAuthBundle\Tests\Controller;

use HWI\Bundle\OAuthBundle\Tests\Fixtures\CustomOAuthToken;

class ConnectControllerConnectServiceActionTest extends AbstractConnectControllerTest
{
    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function testNotEnabled()
    {
        $this->container->setParameter('hwi_oauth.connect', false);

        $this->controller->connectServiceAction($this->request, 'facebook');
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @expectedExceptionMessage Cannot connect an account.
     */
    public function testAlreadyConnected()
    {
        $this->getAuthorizationChecker()->expects($this->once())
            ->method('isGranted')
            ->with('IS_AUTHENTICATED_REMEMBERED')
            ->willReturn(false)
        ;

        $this->controller->connectServiceAction($this->request, 'facebook');
    }

    public function testConnectConfirm()
    {
        $key = time();

        $this->request->query->set('key', $key);

        $this->getAuthorizationChecker()->expects($this->once())
            ->method('isGranted')
            ->with('IS_AUTHENTICATED_REMEMBERED')
            ->willReturn(true)
        ;

        $this->session->expects($this->once())
            ->method('get')
            ->with('_hwi_oauth.connect_confirmation.'.$key)
            ->willReturn(array())
        ;

        $form = $this->getMockBuilder('\Symfony\Component\Form\FormInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->formFactory->expects($this->once())
            ->method('create')
            ->willReturn($form)
        ;

        $this->templating->expects($this->once())
            ->method('renderResponse')
            ->with('HWIOAuthBundle:Connect:connect_confirm.html.twig')
        ;

        $this->controller->connectServiceAction($this->request, 'facebook');
    }

    public function testConnectSuccess()
    {
        $key = time();

        $this->request->query->set('key', $key);
        $this->request->setMethod('POST');

        $this->getAuthorizationChecker()->expects($this->once())
            ->method('isGranted')
            ->with('IS_AUTHENTICATED_REMEMBERED')
            ->willReturn(true)
        ;

        $this->session->expects($this->once())
            ->method('get')
            ->with('_hwi_oauth.connect_confirmation.'.$key)
            ->willReturn(array())
        ;

        $form = $this->getMockBuilder('\Symfony\Component\Form\FormInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->formFactory->expects($this->once())
            ->method('create')
            ->willReturn($form)
        ;

        $form->expects($this->once())->method('isSubmitted')->willReturn(true);
        $form->expects($this->once())->method('isValid')->willReturn(true);

        $this->getTokenStorage()->expects($this->once())
            ->method('getToken')
            ->willReturn(new CustomOAuthToken())
        ;

        $this->templating->expects($this->once())
            ->method('renderResponse')
            ->with('HWIOAuthBundle:Connect:connect_success.html.twig')
        ;

        $this->controller->connectServiceAction($this->request, 'facebook');
    }

    public function testConnectNoConfirmation()
    {
        $key = time();

        $this->request->query->set('key', $key);
        $this->container->setParameter('hwi_oauth.connect.confirmation', false);

        $this->getAuthorizationChecker()->expects($this->once())
            ->method('isGranted')
            ->with('IS_AUTHENTICATED_REMEMBERED')
            ->willReturn(true)
        ;

        $this->session->expects($this->once())
            ->method('get')
            ->with('_hwi_oauth.connect_confirmation.'.$key)
            ->willReturn(array())
        ;

        $this->getTokenStorage()->expects($this->once())
            ->method('getToken')
            ->willReturn(new CustomOAuthToken())
        ;

        $this->templating->expects($this->once())
            ->method('renderResponse')
            ->with('HWIOAuthBundle:Connect:connect_success.html.twig')
        ;

        $this->controller->connectServiceAction($this->request, 'facebook');
    }

    public function testResourceOwnerHandle()
    {
        $key = time();

        $this->request->query->set('key', $key);

        $this->getAuthorizationChecker()->expects($this->once())
            ->method('isGranted')
            ->with('IS_AUTHENTICATED_REMEMBERED')
            ->willReturn(true)
        ;

        $this->resourceOwner->expects($this->once())
            ->method('handles')
            ->willReturn(true)
        ;

        $this->resourceOwner->expects($this->once())
            ->method('getAccessToken')
            ->willReturn(array())
        ;

        $form = $this->getMockBuilder('\Symfony\Component\Form\FormInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->formFactory->expects($this->once())
            ->method('create')
            ->willReturn($form)
        ;

        $this->templating->expects($this->once())
            ->method('renderResponse')
            ->with('HWIOAuthBundle:Connect:connect_confirm.html.twig')
        ;

        $this->controller->connectServiceAction($this->request, 'facebook');
    }
}
