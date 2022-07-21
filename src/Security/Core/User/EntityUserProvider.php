<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Security\Core\User;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * User provider for the ORM that loads users given a mapping between resource
 * owner names and the properties of the entities.
 *
 * @author Alexander <iam.asm89@gmail.com>
 */
final class EntityUserProvider implements UserProviderInterface, OAuthAwareUserProviderInterface
{
    private ObjectManager $em;
    private string $class;
    private ?ObjectRepository $repository = null;

    /**
     * @var array<string, string>
     */
    private array $properties = [
        'identifier' => 'id',
    ];

    /**
     * @param string                $class      User entity class to load
     * @param array<string, string> $properties Mapping of resource owners to properties
     */
    public function __construct(ManagerRegistry $registry, string $class, array $properties, ?string $managerName = null)
    {
        $this->em = $registry->getManager($managerName);
        $this->class = $class;
        $this->properties = array_merge($this->properties, $properties);
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $user = $this->findUser(['username' => $identifier]);

        if (!$user) {
            $exception = new UserNotFoundException(sprintf("User '%s' not found.", $identifier));
            $exception->setUserIdentifier($identifier);

            throw $exception;
        }

        return $user;
    }

    /**
     * Symfony <5.4 BC layer.
     *
     * @param string $username
     *
     * @return UserInterface
     */
    public function loadUserByUsername($username)
    {
        $user = $this->findUser(['username' => $username]);
        if (!$user) {
            throw $this->createUserNotFoundException($username, sprintf("User '%s' not found.", $username));
        }

        return $user;
    }

    public function loadUserByOAuthUserResponse(UserResponseInterface $response): ?UserInterface
    {
        $resourceOwnerName = $response->getResourceOwner()->getName();

        if (!isset($this->properties[$resourceOwnerName])) {
            throw new \RuntimeException(sprintf("No property defined for entity for resource owner '%s'.", $resourceOwnerName));
        }

        $username = method_exists($response, 'getUserIdentifier') ? $response->getUserIdentifier() : $response->getUsername();
        if (null === $user = $this->findUser([$this->properties[$resourceOwnerName] => $username])) {
            throw $this->createUserNotFoundException($username, sprintf("User '%s' not found.", $username));
        }

        return $user;
    }

    public function refreshUser(UserInterface $user): ?UserInterface
    {
        $accessor = PropertyAccess::createPropertyAccessor();
        $identifier = $this->properties['identifier'];
        if (!$accessor->isReadable($user, $identifier) || !$this->supportsClass(\get_class($user))) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $userId = $accessor->getValue($user, $identifier);

        // @phpstan-ignore-next-line Symfony <5.4 BC layer
        $username = method_exists($user, 'getUserIdentifier') ? $user->getUserIdentifier() : $user->getUsername();

        if (null === $user = $this->findUser([$identifier => $userId])) {
            throw $this->createUserNotFoundException($username, sprintf('User with ID "%d" could not be reloaded.', $userId));
        }

        return $user;
    }

    public function supportsClass($class): bool
    {
        return $class === $this->class || is_subclass_of($class, $this->class);
    }

    private function findUser(array $criteria): ?UserInterface
    {
        if (null === $this->repository) {
            $this->repository = $this->em->getRepository($this->class);
        }

        return $this->repository->findOneBy($criteria);
    }

    /**
     * @return UsernameNotFoundException|UserNotFoundException
     */
    private function createUserNotFoundException(string $username, string $message)
    {
        if (class_exists(UserNotFoundException::class)) {
            $exception = new UserNotFoundException($message);
            $exception->setUserIdentifier($username);
        } else {
            if (!class_exists(UsernameNotFoundException::class)) {
                throw new \RuntimeException('Unsupported Symfony version used!');
            }

            $exception = new UsernameNotFoundException($message);
            if (method_exists($exception, 'setUsername')) {
                $exception->setUsername($username); // @phpstan-ignore-this-line Symfony <5.4 BC layer
            }
        }

        return $exception;
    }
}
