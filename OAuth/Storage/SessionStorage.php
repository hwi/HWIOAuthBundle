<?php

namespace HWI\Bundle\OAuthBundle\OAuth\Storage;

use HWI\Bundle\OAuthBundle\OAuth\StorageInterface,
    HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface;

/**
 * Session storage for tokens
 */
class SessionStorage implements StorageInterface
{
    private $session;

    /**
     * @param Session $session
     */
    public function __construct($session)
    {
        $this->session = $session;
    }

    /**
     * {@inheritDoc}
     */
    public function read(ResourceOwnerInterface $resourceOwner, $tokenId)
    {
        $key = $this->generateKey($resourceOwner);
        $requestToken = $this->session->get($key, null);

        if (null !== $requestToken) {
            if ($tokenId == $requestToken['oauth_token']
                || ($requestToken['oauth_expires_in'] > 0
                && $requestToken['timestamp'] + $requestToken['oauth_expires_in'] < time())
            ) {
                $this->session->remove($key);

                return null;
            }
        }

        return $requestToken;
    }

    /**
     * {@inheritDoc}
     */
    public function write(ResourceOwnerInterface $resourceOwner, $token)
    {
        $this->session->set($this->generateKey($resourceOwner), $token);
    }

    protected function generateKey(ResourceOwnerInterface $resourceOwner)
    {
        return implode('.', array(
            '_hwi_oauth',
            $resourceOwner->getName(),
            $resourceOwner->getOption('client_id'),
            'request_token',
        ));
    }
}