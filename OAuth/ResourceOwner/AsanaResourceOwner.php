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
 * AsanaResourceOwner.
 *
 * @author Guillaume Potier <guillaume@wisembly.com>
 */
class AsanaResourceOwner extends GenericOAuth2ResourceOwner
{
    /**
     * {@inheritdoc}
     */
    protected $paths = array(
        'identifier' => 'data.id',
        'nickname' => 'data.name',
        'realname' => 'data.name',
        'email' => 'data.email',
    );

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'authorization_url' => 'https://app.asana.com/-/oauth_authorize',
            'access_token_url' => 'https://app.asana.com/-/oauth_token',
            'infos_url' => 'https://app.asana.com/api/1.0/users/me',
        ));
    }
}
