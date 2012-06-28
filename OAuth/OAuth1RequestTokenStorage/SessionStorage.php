<?php

namespace HWI\Bundle\OAuthBundle\OAuth\OAuth1RequestTokenStorage;

use HWI\Bundle\OAuthBundle\OAuth\OAuth1RequestTokenStorageInterface,
    HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface;

/**
 * Request token storage implementation using the Symfony session.
 *
 * @author Alexander <iam.asm89@gmail.com>
 * @author Francisco Facioni <fran6co@gmail.com>
 */
class SessionStorage implements OAuth1RequestTokenStorageInterface
{
    private $session;

    /**
     * @param mixed $session
     */
    public function __construct($session)
    {
        $this->session = $session;
    }

    /**
     * {@inheritDoc}
     */
    public function fetch(ResourceOwnerInterface $resourceOwner, $tokenId)
    {
        return $this->session->get($this->generateKey($resourceOwner, $tokenId));
    }

    /**
     * {@inheritDoc}
     */
    public function save(ResourceOwnerInterface $resourceOwner, $token)
    {
        $this->session->set($this->generateKey($resourceOwner, $token['oauth_token']), $token);
    }

    /**
     * Key to for fetching or saving a token.
     *
     * @param ResourceOwnerInterface $resourceOwner
     * @param mixed $tokenId
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
