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
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

/**
 * User provider for the ORM that loads users given a mapping between resource
 * owner names and the properties of the entities.
 *
 * @author Alexander <iam.asm89@gmail.com>
 */
class EntityUserProvider implements OAuthAwareUserProviderInterface
{
    /**
     * @var array
     */
    protected $properties;

    /**
     * @var mixed
     */
    protected $repository;

    /**
     * Constructor.
     *
     * @param ManagerRegistry $registry    Manager registry.
     * @param string          $class       User entity class to load.
     * @param array           $properties  Mapping of resource owners to properties
     * @param string          $managerName Optional name of the entitymanager to use
     */
    public function __construct(ManagerRegistry $registry, $class, array $properties, $managerName = null)
    {
        $em = $registry->getManager($managerName);
        $this->repository = $em->getRepository($class);
        $this->properties = $properties;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response, OAuthToken $token)
    {
        $resourceOwnerName = $response->getResourceOwner()->getName();

        if (!isset($this->properties[$resourceOwnerName])) {
            throw new \RuntimeException(sprintf("No property defined for entity for resource owner '%s'.", $resourceOwnerName));
        }

        $username = $response->getUsername();
        $user = $this->repository->findOneBy(array($this->properties[$resourceOwnerName] => $username));

        if (null === $user) {
            throw new UsernameNotFoundException(sprintf("User '%s' not found.", $username));
        }

        return $user;
    }
}
