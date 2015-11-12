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

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * User provider for the ORM that loads users given a mapping between resource
 * owner names and the properties of the entities.
 *
 * @author Alexander <iam.asm89@gmail.com>
 */
class EntityUserProvider implements UserProviderInterface, OAuthAwareUserProviderInterface
{
    /**
     * @var ObjectManager
     */
    protected $em;

    /**
     * @var string
     */
    protected $class;

    /**
     * @var ObjectRepository
     */
    protected $repository;

    /**
     * @var array
     */
    protected $properties = array(
        'identifier' => 'id',
    );

    /**
     * Constructor.
     *
     * @param ManagerRegistry $registry    Manager registry.
     * @param string          $class       User entity class to load.
     * @param array           $properties  Mapping of resource owners to properties
     * @param string          $managerName Optional name of the entity manager to use
     */
    public function __construct(ManagerRegistry $registry, $class, array $properties, $managerName = null)
    {
        $this->em         = $registry->getManager($managerName);
        $this->class      = $class;
        $this->properties = array_merge($this->properties, $properties);
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($username)
    {
        $user = $this->findUser(array('username' => $username));
        if (!$user) {
            throw new UsernameNotFoundException(sprintf("User '%s' not found.", $username));
        }

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $resourceOwnerName = $response->getResourceOwner()->getName();

        if (!isset($this->properties[$resourceOwnerName])) {
            throw new \RuntimeException(sprintf("No property defined for entity for resource owner '%s'.", $resourceOwnerName));
        }

        $username = $response->getUsername();
        if (null === $user = $this->findUser(array($this->properties[$resourceOwnerName] => $username))) {
            throw new UsernameNotFoundException(sprintf("User '%s' not found.", $username));
        }

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user)
    {
        $accessor   = PropertyAccess::createPropertyAccessor();
        $identifier = $this->properties['identifier'];
        if (!$this->supportsClass(get_class($user)) || !$accessor->isReadable($user, $identifier)) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        $userId = $accessor->getValue($user, $identifier);
        if (null === $user = $this->findUser(array($identifier => $userId))) {
            throw new UsernameNotFoundException(sprintf('User with ID "%d" could not be reloaded.', $userId));
        }

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return $class === $this->class || is_subclass_of($class, $this->class);
    }

    /**
     * @param array $criteria
     *
     * @return object
     */
    protected function findUser(array $criteria)
    {
        if (null === $this->repository) {
            $this->repository = $this->em->getRepository($this->class);
        }

        return $this->repository->findOneBy($criteria);
    }
}
