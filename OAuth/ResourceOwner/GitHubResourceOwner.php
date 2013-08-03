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

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * GitHubResourceOwner
 *
 * @author Geoffrey Bachelet <geoffrey.bachelet@gmail.com>
 * @author Alexander <iam.asm89@gmail.com>
 */
class GitHubResourceOwner extends GenericOAuth2ResourceOwner
{
    /**
     * {@inheritDoc}
     */
    protected $paths = array(
        'identifier'     => 'id',
        'nickname'       => 'login',
        'realname'       => 'name',
        'email'          => 'email',
        'profilepicture' => 'avatar_url',
    );

    /**
     * {@inheritDoc}
     */
    public function revokeToken($token)
    {
        $parameters = array(
            'client_id'     => $this->options['client_id'],
            'client_secret' => $this->options['client_secret'],
        );

        /* @var $response \Buzz\Message\Response */
        $response = $this->httpRequest(sprintf('https://api.github.com/applications/%s/tokens/%s', $this->options['client_id'], $token), $parameters);
        if (404 === $response->getStatusCode()) {
            return false;
        }

        $response = $this->getResponseContent($response);
        $response = $this->httpRequest(sprintf('https://api.github.com/authorizations/%s', $response['id']), $parameters, array(), 'DELETE');

        return 204 === $response->getStatusCode();
    }

    /**
     * {@inheritDoc}
     */
    protected function configureOptions(OptionsResolverInterface $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'authorization_url'   => 'https://github.com/login/oauth/authorize',
            'access_token_url'    => 'https://github.com/login/oauth/access_token',
            'infos_url'           => 'https://api.github.com/user',

            'use_commas_in_scope' => true,
        ));
    }
}
