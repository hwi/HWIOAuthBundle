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

use FOS\UserBundle\Model\UserManagerInterface;

use HWI\Bundle\OAuthBundle\Connect\AccountConnectorInterface,
    HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface,
    HWI\Bundle\OAuthBundle\Security\Core\Exception\AccountNotLinkedException,
    HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;

/**
 * Class providing a bridge to use the FOSUB user provider with HWIOAuth.
 *
 * In order to use the class as a connector, the appropriate setters for the 
 * property mapping should be available.
 *
 * @author Alexander <iam.asm89@gmail.com>
 */
class FOSUBUserProvider implements OAuthAwareUserProviderInterface
{
    /**
     * @var UserManagerInterface
     */
    protected $userManager;

    /**
     * @var array
     */
    protected $properties;

    /**
     * Constructor.
     *
     * @param UserManagerInterface $userManager FOSUB user provider.
     * @param array                $properties  Property mapping.
     */
    public function __construct(UserManagerInterface $userManager, array $properties)
    {
        $this->userManager = $userManager;
        $this->properties  = $properties;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $user = $this->userManager->findUserBy(array($this->getProperty($response) => $response->getUsername()));

        if (null === $user) {
            throw new AccountNotLinkedException(sprintf('User "%s" not found.', $response->getUsername()));
        }

        return $user;
    }

    public function connect($user, UserResponseInterface $response)
    {
        $setter = 'set'.ucfirst($this->getProperty($response));

        if (!method_exists($user, $setter)) {
            throw new \RuntimeException(sprintf("Class '%s' should have a method '%s'.", get_class($user), $setter));
        }

        $user->$setter($response->getUsername());

        $this->userManager->updateUser($user);
    }

    protected function getProperty($response)
    {
        $resourceOwnerName = $response->getResourceOwner()->getName();

        if (!isset($this->properties[$resourceOwnerName])) {
            throw new \RuntimeException(sprintf("No property defined for entity for resource owner '%s'.", $resourceOwnerName));
        }

        return $this->properties[$resourceOwnerName];
    }
}
