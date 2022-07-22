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
 * @author Krystian Marcisz <simivar@gmail.com>
 */
final class GeniusResourceOwner extends GenericOAuth2ResourceOwner
{
    public const TYPE = 'genius';

    /**
     * {@inheritdoc}
     */
    protected array $paths = [
        'identifier' => 'response.user.id',
        'nickname' => 'response.user.name',
        'realname' => 'response.user.name',
        'email' => 'response.user.email',
        'profilepicture' => 'response.user.avatar.medium.url',
    ];

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'authorization_url' => 'https://api.genius.com/oauth/authorize',
            'access_token_url' => 'https://api.genius.com/oauth/token',
            'infos_url' => 'https://api.genius.com/account',
            'use_bearer_authorization' => true,
            'use_commas_in_scope' => true,
            'scope' => 'me',
        ]);
    }
}
