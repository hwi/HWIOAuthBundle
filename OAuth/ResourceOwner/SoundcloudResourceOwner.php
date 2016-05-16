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
 * SoundcloudResourceOwner.
 *
 * @author Anthony AHMED <antho.ahmed@gmail.com>
 */
class SoundcloudResourceOwner extends GenericOAuth2ResourceOwner
{
    /**
     * {@inheritdoc}
     */
    protected $paths = array(
        'identifier' => 'id',
        'nickname' => 'username',
        'realname' => 'full_name',
    );

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'access_token_url' => 'https://api.soundcloud.com/oauth2/token',
            'attr_name' => 'oauth_token',
            'authorization_url' => 'https://soundcloud.com/connect',
            'infos_url' => 'https://api.soundcloud.com/me.json',
            'scope' => 'non-expiring',
            'use_bearer_authorization' => false,
        ));
    }
}
