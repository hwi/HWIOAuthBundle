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
 * BufferAppResourceOwner
 *
 * @author João Paulo Cercal <sistemas@cekurte.com>
 */
class BufferAppResourceOwner extends GenericOAuth2ResourceOwner
{
    /**
     * {@inheritDoc}
     */
    protected $paths = array(
        'identifier' => 'id',
        'nickname'   => 'id',
        'realname'   => 'id',
    );

    /**
     * {@inheritDoc}
     */
    protected function configureOptions(OptionsResolverInterface $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'authorization_url' => 'https://bufferapp.com/oauth2/authorize',
            'access_token_url'  => 'https://api.bufferapp.com/1/oauth2/token.json',
            'infos_url'         => 'https://api.bufferapp.com/1/user.json',
        ));
    }
}
