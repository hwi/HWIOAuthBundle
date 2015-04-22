<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Security\Core\Exception;

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * Represents an oauth authentication exception, with more details
 * such as the redirectURI or the resource owner's name if available
 *
 * @author Baptiste Clavié <clavie.b@gmail.com>
 */
class OAuthAuthenticationException extends AuthenticationException
{
    /**
     * @var string
     */
    private $redirectUri;

    /**
     * @var string
     */
    private $resourceOwnerName;

    public function __construct($message, $redirectUri, $resourceOwner)
    {
        parent::__construct($message);

        if ($resourceOwner instanceof ResourceOwnerInterface) {
            $resourceOwner = $resourceOwner->getName();
        }

        $this->redirectUri = $redirectUri;
        $this->resourceOwnerName = $resourceOwner;
    }

    /**
     * Get the redirect uri the request was supposed to redirect to
     *
     * @return string
     */
    public function getRedirectUri()
    {
        return $this->redirectUri;
    }

    /**
     * Get the resource name that triggered this exception
     *
     * @return string
     */
    public function getResourceOwnerName()
    {
        return $this->resourceOwnerName;
    }
}

