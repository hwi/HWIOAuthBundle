<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\OAuth\RequestDataStorage;

use HWI\Bundle\OAuthBundle\OAuth\RequestDataStorageInterface;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Request token storage implementation using the Symfony session.
 *
 * @author Alexander <iam.asm89@gmail.com>
 * @author Francisco Facioni <fran6co@gmail.com>
 * @author Joseph Bielawski <stloyd@gmail.com>
 */
class SessionStorage implements RequestDataStorageInterface
{
    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @param SessionInterface $session
     */
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * {@inheritdoc}
     */
    public function fetch(ResourceOwnerInterface $resourceOwner, $key, $type = 'token')
    {
        $key = $this->generateKey($resourceOwner, $key, $type);
        if (null === $token = $this->session->get($key)) {
            throw new \InvalidArgumentException('No data available in storage.');
        }

        // request tokens are one time use only
        $this->session->remove($key);

        return $token;
    }

    /**
     * {@inheritdoc}
     */
    public function save(ResourceOwnerInterface $resourceOwner, $value, $type = 'token')
    {
        if ('token' === $type) {
            if (!is_array($value) || !isset($value['oauth_token'])) {
                throw new \InvalidArgumentException('Invalid request token.');
            }

            $key = $this->generateKey($resourceOwner, $value['oauth_token'], 'token');
        } else {
            $key = $this->generateKey($resourceOwner, is_array($value) ? reset($value) : $value, $type);
        }

        $this->session->set($key, $value);
    }

    /**
     * Key to for fetching or saving a token.
     *
     * @param ResourceOwnerInterface $resourceOwner
     * @param string                 $key
     * @param string                 $type
     *
     * @return string
     */
    protected function generateKey(ResourceOwnerInterface $resourceOwner, $key, $type)
    {
        return sprintf('_hwi_oauth.%s.%s.%s.%s', $resourceOwner->getName(), $resourceOwner->getOption('client_id'), $type, $key);
    }
}
