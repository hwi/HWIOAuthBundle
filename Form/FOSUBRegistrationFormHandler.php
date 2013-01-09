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
    protected $tokenGenerator;
    protected $iterations;

    /**
     * Constructor.
     *
     * @param UserManagerInterface    $userManager             FOSUB user manager
     * @param MailerInterface         $mailer                  FOSUB mailer
     * @param TokenGenerator          $tokenGenerator          FOSUB token generator
     * @param integer                 $iterations              Amount of attempts that should be made to 'guess' a unique username
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
        $form->setData($userInformation);

        // if the form is not posted we'll try to set some properties
        if ($request->isMethod('POST')) {
            $form->bind($request);

            if ($form->isValid()) {
                $user = $form->getData();

                $user->setUsername($this->getUniqueUsername($userInformation->getNickname()));

                if ($userInformation instanceof AdvancedUserResponseInterface && method_exists($user, 'setEmail')) {
                    $user->setEmail($userInformation->getEmail());
                }

                return true;
            }
        }

        return false;
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

}
