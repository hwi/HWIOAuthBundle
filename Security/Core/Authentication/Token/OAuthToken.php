<?php

/*
 * This file is part of the KnpOAuthBundle package.
 *
 * (c) KnpLabs <hello@knplabs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Knp\Bundle\OAuthBundle\Security\Core\Authentication\Token;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

/**
 * OAuthToken
 *
 * @author Geoffrey Bachelet <geoffrey.bachelet@gmail.com>
 */
class OAuthToken extends AbstractToken
{
    /**
     * @var string
     */
    private $accessToken;

    /**
     * @param string $accessToken The OAuth access token
     * @param array $roles
     */
    public function __construct($accessToken, array $roles = array())
    {
        $this->accessToken = $accessToken;

        parent::__construct($roles);
    }

    /**
     * {@inheritDoc}
     */
    public function getCredentials()
    {
        return $this->accessToken;
    }

    /**
     * {@inheritDoc}
     */
    public function isAuthenticated()
    {
        return count($this->getRoles()) > 0;
    }
}