<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\OAuth\ResourceOwner;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Indra Gunawan <hello@indra.my.id>
 */
final class GitLabResourceOwner extends GenericOAuth2ResourceOwner
{
    public const TYPE = 'gitlab';

    /**
     * {@inheritdoc}
     */
    protected array $paths = [
        'identifier' => 'id',
        'nickname' => 'username',
        'realname' => 'name',
        'email' => 'email',
        'profilepicture' => 'avatar_url',
    ];

    /**
     * {@inheritdoc}
     */
    public function revokeToken($token)
    {
        $parameters = [
            'token' => $token,
            'client_id' => $this->options['client_id'],
            'client_secret' => $this->options['client_secret'],
        ];

        $response = $this->httpRequest(
            $this->options['revoke_token_url'],
            $parameters,
            [],
            'POST'
        );

        return 200 === $response->getStatusCode();
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'authorization_url' => 'https://gitlab.com/oauth/authorize',
            'access_token_url' => 'https://gitlab.com/oauth/token',
            'revoke_token_url' => 'https://gitlab.com/oauth/revoke',
            'infos_url' => 'https://gitlab.com/api/v4/user',

            'scope' => 'read_user',
            'use_commas_in_scope' => false,
            'use_bearer_authorization' => true,
        ]);
    }
}
