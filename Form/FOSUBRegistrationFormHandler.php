<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Form;

use FOS\UserBundle\Form\Handler\RegistrationFormHandler,
    FOS\UserBundle\Model\UserManagerInterface,
    FOS\UserBundle\Mailer\MailerInterface;

use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;

use Symfony\Component\Form\Form,
    Symfony\Component\HttpFoundation\Request;

/**
 * FormHandlerInterface
 *
 * Interface for objects that are able to handle a form.
 *
 * @author Alexander <iam.asm89@gmail.com>
 */
class FOSUBRegistrationFormHandler implements RegistrationFormHandlerInterface
{
    protected $userManager;
    protected $mailer;
    protected $registrationFormHandler;

    public function __construct(RegistrationFormHandler $registrationFormHandler, UserManagerInterface $userManager, MailerInterface $mailer)
    {
        $this->registrationFormHandler = $registrationFormHandler;
        $this->userManager = $userManager;
        $this->mailer = $mailer;
    }

    /**
     * Processes the form for a given request.
     *
     * @param Request $request Active request
     * @param Form    $form    Form to process
     *
     * @return boolean True if the processing was successful
     */
    public function process(Request $request, Form $form, UserResponseInterface $userInformation)
    {
        $formHandler = $this->getReconstructionFormHandler($request, $form);

        return $formHandler->process();
    }

    protected function getReconstructionFormHandler(Request $request, Form $form)
    {
        $handlerClass = get_class($this->registrationFormHandler);

        return new $handlerClass($form, $request, $this->userManager, $this->mailer);
    }

}
