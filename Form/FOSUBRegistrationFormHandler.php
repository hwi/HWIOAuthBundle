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
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpKernel\Kernel,
    Symfony\Component\Security\Core\User\UserInterface;

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

    /**
     * Constructor.
     *
     * @param UserManagerInterface $userManager    FOSUB user manager
     * @param MailerInterface      $mailer         FOSUB mailer
     * @param TokenGenerator       $tokenGenerator FOSUB token generator
     * @param integer              $iterations     Amount of attempts that should be made to 'guess' a unique username
     */
    public function __construct(UserManagerInterface $userManager, MailerInterface $mailer, TokenGenerator $tokenGenerator = null, $iterations = 5)
    {
        $this->userManager = $userManager;
        $this->mailer = $mailer;
        $this->tokenGenerator = $tokenGenerator;
        $this->iterations = $iterations;
    }

    /**
     * {@inheritDoc}
     */
    public function process(Request $request, Form $form, UserResponseInterface $userInformation)
    {
        if (null !== $this->registrationFormHandler) {
            $formHandler = $this->reconstructFormHandler($request, $form);

            // make FOSUB process the form already
            $processed = $formHandler->process();

            // if the form is not posted we'll try to set some properties
            if ('POST' === $request->getMethod()) {
                $user = $this->setUserInformation($form->getData(), $userInformation);

                $form->setData($user);
            }

            return $processed;
        }

        $form->setData($userInformation);

        if ('POST' === $request->getMethod()) {
            if ('2' == Kernel::MAJOR_VERSION && '2' <= Kernel::MINOR_VERSION) {
                $form->bind($request);
            } else {
                $form->bindRequest($request);
            }

            if ($form->isValid()) {
                $this->setUserInformation($form->getData(), $userInformation);

                return true;
            }
        }

        return false;
    }

    /**
     * Set registration form handler.
     *
     * @param null|RegistrationFormHandler $registrationFormHandler FOSUB registration form handler
     */
    public function setFormHandler(RegistrationFormHandler $registrationFormHandler = null)
    {
        $this->registrationFormHandler = $registrationFormHandler;
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

    /**
     * Set user information from form
     *
     * @param UserInterface         $user
     * @param UserResponseInterface $userInformation
     *
     * @return UserInterface
     */
    protected function setUserInformation(UserInterface $user, UserResponseInterface $userInformation)
    {
        $user->setUsername($this->getUniqueUsername($userInformation->getNickname()));

        if ($userInformation instanceof AdvancedUserResponseInterface && method_exists($user, 'setEmail')) {
            $user->setEmail($userInformation->getEmail());
        }

        return $user;
    }
}
