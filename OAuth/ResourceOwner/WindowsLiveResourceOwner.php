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
 * WindowsLiveResourceOwner
 *
 * @author Alexander <iam.asm89@gmail.com>
 */
class WindowsLiveResourceOwner extends GenericOAuth2ResourceOwner
{
    /**
     * {@inheritDoc}
     */
    protected $paths = array(
        'identifier' => 'id',
        'nickname'   => 'name',
        'realname'   => 'name',
        'email'      => 'emails.account', // requires 'wl.emails' scope
    );

    /**
     * {@inheritDoc}
     */
    protected function configureOptions(OptionsResolverInterface $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'authorization_url' => 'https://login.live.com/oauth20_authorize.srf',
            'access_token_url'  => 'https://login.live.com/oauth20_token.srf',
            'infos_url'         => 'https://apis.live.net/v5.0/me',
        ));
    }
}
