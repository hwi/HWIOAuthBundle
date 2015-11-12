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
 * XingResourceOwner
 *
 * @author othillo <othillo@othillo.nl>
 */
class XingResourceOwner extends GenericOAuth1ResourceOwner
{
    /**
     * {@inheritDoc}
     */
    protected $paths = array(
        'identifier'     => 'users.0.id',
        'nickname'       => 'users.0.display_name',
        'firstname'      => 'users.0.first_name',
        'lastname'       => 'users.0.last_name',
        'realname'       => array('users.0.first_name', 'users.0.last_name'),
        'profilepicture' => 'users.0.photo_urls.large',
        'email'          => 'users.0.active_email',
    );

    /**
     * {@inheritDoc}
     */
    protected function configureOptions(OptionsResolverInterface $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'authorization_url' => 'https://api.xing.com/v1/authorize',
            'request_token_url' => 'https://api.xing.com/v1/request_token',
            'access_token_url'  => 'https://api.xing.com/v1/access_token',
            'infos_url'         => 'https://api.xing.com/v1/users/me',
        ));
    }
}
