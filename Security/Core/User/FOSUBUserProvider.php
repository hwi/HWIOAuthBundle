<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Security\Core\User;

use FOS\UserBundle\Model\User;
use FOS\UserBundle\Model\UserManagerInterface;
use HWI\Bundle\OAuthBundle\Connect\AccountConnectorInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\Exception\AccountNotLinkedException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Class providing a bridge to use the FOSUB user provider with HWIOAuth.
 *
 * In order to use the class as a connector, the appropriate setters for the
 * property mapping should be available.
 *
 * @author Alexander <iam.asm89@gmail.com>
 */
class FOSUBUserProvider implements UserProviderInterface, AccountConnectorInterface, OAuthAwareUserProviderInterface
{
    /**
     * @var UserManagerInterface
     */
    protected $userManager;

    /**
     * @var array
     */
    protected $properties = array(
        'identifier' => 'id',
    );

    /**
     * @var PropertyAccessor
     */
    protected $accessor;

    /**
     * Constructor.
     *
     * @param UserManagerInterface $userManager FOSUB user provider.
     * @param array                $properties  Property mapping.
     */
    public function __construct(UserManagerInterface $userManager, array $properties)
    {
        $this->userManager = $userManager;
        $this->properties  = array_merge($this->properties, $properties);
        $this->accessor    = PropertyAccess::createPropertyAccessor();
    }

    /**
     * {@inheritDoc}
     */
    public function loadUserByUsername($username)
    {
        // Compatibility with FOSUserBundle < 2.0
        if (class_exists('FOS\UserBundle\Form\Handler\RegistrationFormHandler')) {
            return $this->userManager->loadUserByUsername($username);
        }

        return $this->userManager->findUserByUsername($username);
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $username = $response->getUsername();

        $user = $this->userManager->findUserBy(array($this->getProperty($response) => $username));
        if (null === $user || null === $username) {
            throw new AccountNotLinkedException(sprintf("User '%s' not found.", $username));
        }

        return $user;
    }

    /**
     * {@inheritDoc}
     */
    public function connect(UserInterface $user, UserResponseInterface $response)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Expected an instance of FOS\UserBundle\Model\User, but got "%s".', get_class($user)));
        }

        $property = $this->getProperty($response);

        // Symfony <2.5 BC
        if (method_exists($this->accessor, 'isWritable') && !$this->accessor->isWritable($user, $property)
            || !method_exists($this->accessor, 'isWritable') && !method_exists($user, 'set'.ucfirst($property))) {
            throw new \RuntimeException(sprintf("Class '%s' must have defined setter method for property: '%s'.", get_class($user), $property));
        }

        $username = $response->getUsername();

        if (null !== $previousUser = $this->userManager->findUserBy(array($property => $username))) {
            $this->disconnect($previousUser, $response);
        }

        $this->accessor->setValue($user, $property, $username);

        $this->userManager->updateUser($user);
    }
    
    /**
     * Disconnects a user
     * 
     * @param UserInterface $user
     * @param UserResponseInterface $response
     */
    public function disconnect(UserInterface $user, UserResponseInterface $response)
    {
        $property = $this->getProperty($response);

        $this->accessor->setValue($user, $property, null);
        $this->userManager->updateUser($user);
    }

    /**
     * {@inheritDoc}
     */
    public function refreshUser(UserInterface $user)
    {
        // Compatibility with FOSUserBundle < 2.0
        if (class_exists('FOS\UserBundle\Form\Handler\RegistrationFormHandler')) {
            return $this->userManager->refreshUser($user);
        }

        $identifier = $this->properties['identifier'];
        if (!$user instanceof User || !$this->accessor->isReadable($user, $identifier)) {
            throw new UnsupportedUserException(sprintf('Expected an instance of FOS\UserBundle\Model\User, but got "%s".', get_class($user)));
        }

        $userId = $this->accessor->getValue($user, $identifier);
        if (null === $user = $this->userManager->findUserBy(array($identifier => $userId))) {
            throw new UsernameNotFoundException(sprintf('User with ID "%d" could not be reloaded.', $userId));
        }

        return $user;
    }

    /**
     * {@inheritDoc}
     */
    public function supportsClass($class)
    {
        $userClass = $this->userManager->getClass();

        return $userClass === $class || is_subclass_of($class, $userClass);
    }

    /**
     * Gets the property for the response.
     *
     * @param UserResponseInterface $response
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    protected function getProperty(UserResponseInterface $response)
    {
        $resourceOwnerName = $response->getResourceOwner()->getName();

        if (!isset($this->properties[$resourceOwnerName])) {
            throw new \RuntimeException(sprintf("No property defined for entity for resource owner '%s'.", $resourceOwnerName));
        }

        return $this->properties[$resourceOwnerName];
    }
}
