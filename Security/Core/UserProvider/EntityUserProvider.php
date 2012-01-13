<?php

/*
 * This file is part of the KnpOAuthBundle package.
 *
 * (c) KnpLabs <hello@knplabs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Knp\Bundle\OAuthBundle\Security\Core\UserProvider;

use Symfony\Component\Security\Core\Exception\UsernameNotFoundException,
    Symfony\Bridge\Doctrine\Security\User\EntityUserProvider as BaseEntityUserProvider,
    Doctrine\Common\Persistence\ManagerRegistry;

/**
 * EntityUserProvider
 *
 * @author Geoffrey Bachelet <geoffrey.bachelet@gmail.com>
 */
class EntityUserProvider extends BaseEntityUserProvider
{
    private $class;
    private $property;

    public function __construct(ManagerRegistry $registry, $class, $property = null, $managerName = null)
    {
        $this->property = $property;
        $this->class = $class;

        $this->em = $registry->getManager($managerName);

        if (false !== strpos($this->class, ':')) {
            $metadata = $this->em->getClassMetadata($this->class);
            $this->class = $metadata->getName();
        }

        parent::__construct($registry, $class, $property, $managerName);
    }

    /**
     * @var string $username
     * @return mixed An user entity
     */
    public function createEntity($username)
    {
        $setter = 'set'.ucfirst($this->property);
        $user   = new $this->class;

        call_user_func(array($user, $setter), $username);

        return $user;
    }

    /**
     * {@inheritDoc}
     */
    public function loadUserByUsername($username)
    {
        try {
            $user = parent::loadUserByUsername($username);
        } catch (UsernameNotFoundException $e) {
            $user = $this->createEntity($username);
            $this->em->persist($user);
            $this->em->flush();
        }

        return $user;
    }
}