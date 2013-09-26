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
 * StackExchangeResourceOwner
 *
 * @author Joseph Bielawski <stloyd@gmail.com>
 */
class StackExchangeResourceOwner extends GenericOAuth2ResourceOwner
{
    /**
     * {@inheritDoc}
     */
    protected $paths = array(
        'identifier'     => 'user_id',
        'nickname'       => 'display_name',
        'realname'       => 'display_name',
        'profilepicture' => 'profile_image',
    );

    /**
     * {@inheritDoc}
     */
    protected function configureOptions(OptionsResolverInterface $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'authorization_url' => 'https://stackexchange.com/oauth',
            'access_token_url'  => 'https://stackexchange.com/oauth/access_token',
            'infos_url'         => 'https://api.stackexchange.com/2.0/me',

            'scope'             => 'no_expiry',
        ));
    }
}
