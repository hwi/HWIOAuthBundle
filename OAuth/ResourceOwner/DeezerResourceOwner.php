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

use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * @author Kieu Anh Tuan <passkey1510@gmail.com>
 */
class DeezerResourceOwner extends GenericOAuth2ResourceOwner
{
    /**
     * {@inheritDoc}
     */
    protected $paths = array(
        'identifier'     => 'id',
        'nickname'       => 'name',
        'realname'       => 'firstname',
        'email'          => 'email',
        'firstname'      => 'firstname',
        'lastname'       => 'lastname',
        'profilepicture' => 'picture',
        'gender'         => 'gender'
    );

    /**
     * {@inheritDoc}
     */
    protected function configureOptions(OptionsResolverInterface $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'authorization_url' => 'https://connect.deezer.com/oauth/auth.php',
            'access_token_url'  => 'https://connect.deezer.com/oauth/access_token.php',
            'infos_url'         => 'https://api.deezer.com/user/me',
            'use_bearer_authorization' => false
        ));
    }
}

