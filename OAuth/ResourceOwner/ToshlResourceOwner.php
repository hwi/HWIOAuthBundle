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

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * ToshlResourceOwner.
 *
 * @author Davide Bellettini <davide@bellettini.me>
 */
class ToshlResourceOwner extends GenericOAuth2ResourceOwner
{
    /**
     * {@inheritdoc}
     */
    protected $paths = array(
        'identifier' => 'id',
        'nickname' => 'email',
        'firstname' => 'first_name',
        'lastname' => 'last_name',
        'realname' => array('first_name', 'last_name'),
        'email' => 'email',
    );

    /**
     * {@inheritdoc}
     */
    public function revokeToken($token)
    {
        /* @var $response \Psr\Http\Message\ResponseInterface */
        $response = $this->httpRequest(
            $this->options['revoke_token_url'],
            null,
            array('Authorization' => 'Basic '.base64_encode($this->options['client_id'].':'.$this->options['client_secret'])),
            'DELETE'
        );

        return 204 === $response->getStatusCode();
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'authorization_url' => 'https://toshl.com/oauth2/authorize',
            'access_token_url' => 'https://toshl.com/oauth2/token',
            'revoke_token_url' => 'https://toshl.com/oauth2/revoke',
            'infos_url' => 'https://api.toshl.com/me',
            'csrf' => true,
            'use_commas_in_scope' => true,
        ));
    }
}
