<?php

/*
 * This file is part of the KnpOAuthBundle package.
 *
 * (c) KnpLabs <hello@knplabs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Knp\Bundle\OAuthBundle\Security\Core\User;

use Symfony\Component\Security\Core\User\UserProviderInterface,
    Symfony\Component\Security\Core\User\UserInterface,
    Symfony\Component\Security\Core\Exception\UnsupportedUserException;

use Doctrine\ORM\EntityManager;

/**
 * EntityUserProvider
 *
 * @author Geoffrey Bachelet <geoffrey.bachelet@gmail.com>
 */
abstract class EntityUserProvider implements UserProviderInterface
{
    /**
     * var Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @return string
     */
    abstract public function getEntityClass();

    /**
     * @var Doctrine\ORM\EntityManager
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @var string $username
     * @return mixed An user entity
     */
    public function createEntity($username)
    {
        $user = new {$this->getEntityClass()};
        $user->username = $username;

        return $user;
    }

    /**
     * {@inheritDoc}
     */
    public function loadUserByUsername($username)
    {
        $user = $this->em->getRepository($this->getEntityClass())->findOneByUsername($username);

        if (!$user) {
            $user = $this->createEntity($username);
            $this->em->persist($user);
        }

        return $user;
    }

    /**
     * {@inheritDoc}
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$this->supportsClass(get_class($user)) {
            throw new UnsupportedUserException(sprintf('Unsupported user class "%s"', get_class($user));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * {@inheritDoc}
     */
    public function supportsClass($class)
    {
        return $class == $this->getEntityClass();
    }
}