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

final class DiscogsResourceOwner extends GenericOAuth1ResourceOwner
{
    public const TYPE = 'discogs';

    /**
     * {@inheritdoc}
     */
    protected array $paths = [
        'identifier' => 'id',
        'nickname' => 'username',
    ];

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'authorization_url' => 'https://www.discogs.com/oauth/authorize',
            'request_token_url' => 'https://api.discogs.com/oauth/request_token',
            'access_token_url' => 'https://api.discogs.com/oauth/access_token',
            'infos_url' => 'https://api.discogs.com/oauth/identity',
        ]);
    }
}
