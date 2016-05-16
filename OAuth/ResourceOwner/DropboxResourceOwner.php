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
 * DropboxResourceOwner.
 *
 * @author Jamie Sutherland<me@jamiesutherland.com>
 */
class DropboxResourceOwner extends GenericOAuth2ResourceOwner
{
    /**
     * {@inheritdoc}
     */
    protected $paths = array(
        'identifier' => 'uid',
        'nickname' => 'email',
        'realname' => 'display_name',
        'email' => 'email',
    );

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'authorization_url' => 'https://www.dropbox.com/1/oauth2/authorize',
            'access_token_url' => 'https://api.dropbox.com/1/oauth2/token',
            'infos_url' => 'https://api.dropbox.com/1/account/info',
        ));
    }
}
