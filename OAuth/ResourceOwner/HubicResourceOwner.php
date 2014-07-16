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
 * HubicResourceOwner
 *
 * @author Vincent Cassé <vincent@casse.me>
 */
class HubicResourceOwner extends GenericOAuth2ResourceOwner
{
    /**
     * {@inheritDoc}
     */
    protected $paths = array(
        'identifier' => 'email',
        'nickname'   => 'email',
        'realname'   => 'firstname',
        'email'      => 'email',
    );

    /**
     * {@inheritDoc}
     */
    protected function configureOptions(OptionsResolverInterface $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'authorization_url' => 'https://api.hubic.com/oauth/auth/',
            'access_token_url'  => 'https://api.hubic.com/oauth/token/',
            'infos_url'         => 'https://api.hubic.com/1.0/account',
        ));
    }
}
