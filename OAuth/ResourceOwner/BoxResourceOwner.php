<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\OAuth\ResourceOwner;

use Buzz\Message\RequestInterface as HttpRequestInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * BoxResourceOwner
 *
 * @author Joseph Bielawski <stloyd@gmail.com>
 */
class BoxResourceOwner extends GenericOAuth2ResourceOwner
{
    /**
     * {@inheritDoc}
     */
    protected $paths = array(
        'identifier'     => 'id',
        'nickname'       => 'name',
        'realname'       => 'name',
        'email'          => 'login',
        'profilepicture' => 'avatar_url'
    );

    /**
     * {@inheritDoc}
     */
    public function revokeToken($token)
    {
        $parameters = array(
            'client_id'     => $this->options['client_id'],
            'client_secret' => $this->options['client_secret'],
            'token'         => $token
        );

        $response = $this->httpRequest($this->normalizeUrl($this->options['revoke_token_url']), $parameters, array(), HttpRequestInterface::METHOD_POST);

        return 200 === $response->getStatusCode();
    }

    /**
     * {@inheritDoc}
     */
    protected function configureOptions(OptionsResolverInterface $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'authorization_url' => 'https://www.box.com/api/oauth2/authorize',
            'access_token_url'  => 'https://www.box.com/api/oauth2/token',
            'revoke_token_url'  => 'https://www.box.com/api/oauth2/revoke',
            'infos_url'         => 'https://api.box.com/2.0/users/me',
        ));
    }
}
