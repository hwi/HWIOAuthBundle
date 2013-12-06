<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\OAuth\OAuth1RequestTokenStorage;

use HWI\Bundle\OAuthBundle\OAuth\OAuth1RequestTokenStorageInterface;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface;

/**
 * Request token storage implementation using the Symfony session.
 *
 * @author Alexander <iam.asm89@gmail.com>
 * @author Francisco Facioni <fran6co@gmail.com>
 */
class SessionStorage implements OAuth1RequestTokenStorageInterface
{
    private $session;

    public function __construct($session)
    {
        $this->session = $session;
    }

    /**
     * {@inheritDoc}
     */
    public function fetch(ResourceOwnerInterface $resourceOwner, $tokenId)
    {
        $key = $this->generateKey($resourceOwner, $tokenId);
        if (null === $token = $this->session->get($key)) {
            throw new \RuntimeException('No request token available in storage.');
        }

        // request tokens are one time use only
        $this->session->remove($key);

        return $token;
    }

    /**
     * {@inheritDoc}
     */
    public function save(ResourceOwnerInterface $resourceOwner, array $token)
    {
        if (!isset($token['oauth_token'])) {
            throw new \RuntimeException('Invalid request token.');
        }

        $this->session->set($this->generateKey($resourceOwner, $token['oauth_token']), $token);
    }

    /**
     * Key to for fetching or saving a token.
     *
     * @param ResourceOwnerInterface $resourceOwner
     * @param string                 $tokenId
     *
     * @return string
     */
    protected function generateKey(ResourceOwnerInterface $resourceOwner, $tokenId)
    {
        return implode('.', array(
            '_hwi_oauth.request_token',
            $resourceOwner->getName(),
            $resourceOwner->getOption('client_id'),
            $tokenId,
        ));
    }
}
