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
 * StravaResourceOwner
 *
 * @author Artem Genvald <genvaldartem@gmail.com>
 */
class StravaResourceOwner extends GenericOAuth2ResourceOwner
{
    /**
     * {@inheritDoc}
     */
    protected $paths = array(
        'identifier'     => 'id',
        'realname'       => array('firstname', 'lastname'),
        'profilepicture' => 'profile_medium',
        'email'          => 'email'
    );

    /**
     * {@inheritDoc}
     */
    protected function configureOptions(OptionsResolverInterface $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'authorization_url' => 'https://www.strava.com/oauth/authorize',
            'access_token_url'  => 'https://www.strava.com/oauth/token',
            'infos_url'         => 'https://www.strava.com/api/v3/athlete'
        ));
    }
}
