<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Form;

use FOS\UserBundle\Mailer\MailerInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use FOS\UserBundle\Util\TokenGenerator;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author Alexander <iam.asm89@gmail.com>
 */
class FOSUBRegistrationFormHandler implements RegistrationFormHandlerInterface
{
    /**
     * @var UserManagerInterface
     */
    protected $userManager;

    /**
     * @var MailerInterface
     */
    protected $mailer;

    /**
     * @var TokenGenerator
     */
    protected $tokenGenerator;

    /**
     * @var int
     */
    protected $iterations;

    /**
     * @param UserManagerInterface $userManager    FOSUB user manager
     * @param MailerInterface      $mailer         FOSUB mailer
     * @param TokenGenerator       $tokenGenerator FOSUB token generator
     * @param int                  $iterations     Amount of attempts that should be made to 'guess' a unique username
     */
    public function __construct(UserManagerInterface $userManager, MailerInterface $mailer, TokenGenerator $tokenGenerator = null, $iterations = 5)
    {
        $this->userManager = $userManager;
        $this->mailer = $mailer;
        $this->tokenGenerator = $tokenGenerator;
        $this->iterations = $iterations;
    }

    /**
     * {@inheritdoc}
     */
    public function process(Request $request, Form $form, UserResponseInterface $userInformation)
    {
        $user = $this->userManager->createUser();
        $user->setEnabled(true);

        $form->setData($this->setUserInformation($user, $userInformation));

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            return $form->isValid();
        }

        return false;
    }

    /**
     * Attempts to get a unique username for the user.
     *
     * @param string $name
     *
     * @return string name, or empty string if it failed after all the iterations
     */
    protected function getUniqueUserName($name)
    {
        $i = 0;
        $testName = $name;

        do {
            $user = $this->userManager->findUserByUsername($testName);
        } while (null !== $user && $i < $this->iterations && $testName = $name.++$i);

        return null !== $user ? '' : $testName;
    }

    /**
     * Set user information from form.
     *
     * @param UserInterface         $user
     * @param UserResponseInterface $userInformation
     *
     * @return UserInterface
     */
    protected function setUserInformation(UserInterface $user, UserResponseInterface $userInformation)
    {
        $accessor = PropertyAccess::createPropertyAccessor();
        $accessor->setValue($user, 'username', $this->getUniqueUserName($userInformation->getNickname()));

        if (method_exists($accessor, 'isWritable') && $accessor->isWritable($user, 'email')) {
            $accessor->setValue($user, 'email', $userInformation->getEmail());
        } elseif (method_exists($user, 'setEmail')) {
            $user->setEmail($userInformation->getEmail());
        }

        return $user;
    }
}
