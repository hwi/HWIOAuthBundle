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
 * SlackResourceOwner.
 *
 * @author Baptiste Clavié <clavie.b@gmail.com>
 */
class SlackResourceOwner extends GenericOAuth2ResourceOwner
{
    /**
     * {@inheritdoc}
     */
    protected $paths = array(
        'identifier' => 'user.id',
        'nickname' => 'user.name',
        'email' => 'user.email',
    );

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'authorization_url' => 'https://slack.com/oauth/authorize',
            'access_token_url' => 'https://slack.com/api/oauth.access',
            'infos_url' => 'https://slack.com/api/users.identity',

            'scope' => 'identify',

            'use_bearer_authorization' => false,
            'attr_name' => 'token',
        ));
    }
}
