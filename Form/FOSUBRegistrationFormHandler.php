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
    FOS\UserBundle\Mailer\MailerInterface,
    FOS\UserBundle\Util\TokenGenerator;

use HWI\Bundle\OAuthBundle\OAuth\Response\AdvancedUserResponseInterface,
    HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;

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
    protected $tokenGenerator;
    protected $iterations;
    protected $tokenGenerator;

    /**
     * Constructor.
     *
     * @param RegistrationFormHandler $registrationFormHandler FOSUB registration form handler
     * @param UserManagerInterface    $userManager             FOSUB user manager
     * @param MailerInterface         $mailer                  FOSUB mailer
     * @param TokenGenerator          $tokenGenerator          FOSUB token generator
     * @param integer                 $iterations              Amount of attempts that should be made to 'guess' a unique username
     */
    public function __construct(RegistrationFormHandler $registrationFormHandler, UserManagerInterface $userManager, MailerInterface $mailer, TokenGenerator $tokenGenerator = null, $iterations = 5)
    {
        $this->registrationFormHandler = $registrationFormHandler;
        $this->userManager = $userManager;
        $this->mailer = $mailer;
        $this->tokenGenerator = $tokenGenerator;
        $this->iterations = $iterations;
        $this->tokenGenerator = $tokenGenerator;
    }

    /**
     * Processes the form for a given request.
     *
     * @param Request               $request         Active request
     * @param Form                  $form            Form to process
     * @param UserResponseInterface $userInformation OAuth response
     *
     * @return boolean True if the processing was successful
     */
    public function process(Request $request, Form $form, UserResponseInterface $userInformation)
    {
        $formHandler = $this->reconstructFormHandler($request, $form);

        // make FOSUB process the form already
        $processed = $formHandler->process();

        // if the form is not posted we'll try to set some properties
        if ('POST' !== $request->getMethod()) {
            $user = $form->getData();

            $user->setUsername($this->getUniqueUsername($userInformation->getDisplayName()));

            if ($userInformation instanceof AdvancedUserResponseInterface) {
                $user->setEmail($userInformation->getEmail());
            }

            $form->setData($user);
        }

        return $processed;
    }

    /**
     * Attempts to get a unique username for the user.
     *
     * @param string $name
     *
     * @return string Name, or empty string if it failed after all the iterations.
     */
    protected function getUniqueUserName($name)
    {
        $i = 0;
        $testName = $name;

        do {
            $user = $this->userManager->findUserByUsername($testName);
        } while ($user !== null && $i < $this->iterations && $testName = $name.++$i);

        return $user !== null ? '' : $testName;
    }

    /**
     * Reconstructs the form handler in order to inject the right form.
     *
     * @param Request $request Active request
     * @param Form    $form    Form to process
     *
     * @return mixed
     */
    protected function reconstructFormHandler(Request $request, Form $form)
    {
        $handlerClass = get_class($this->registrationFormHandler);

        return new $handlerClass($form, $request, $this->userManager, $this->mailer, $this->tokenGenerator);
    }

}
