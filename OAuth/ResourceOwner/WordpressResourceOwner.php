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
 * WordpressResourceOwner.
 *
 * @author Joseph Bielawski <stloyd@gmail.com>
 */
class WordpressResourceOwner extends GenericOAuth2ResourceOwner
{
    /**
     * {@inheritdoc}
     */
    protected $paths = array(
        'identifier' => 'ID',
        'nickname' => 'username',
        'realname' => 'display_name',
        'email' => 'email',
        'profilepicture' => 'avatar_URL',
    );

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'authorization_url' => 'https://public-api.wordpress.com/oauth2/authorize',
            'access_token_url' => 'https://public-api.wordpress.com/oauth2/token',
            'infos_url' => 'https://public-api.wordpress.com/rest/v1/me',
        ));
    }
}
