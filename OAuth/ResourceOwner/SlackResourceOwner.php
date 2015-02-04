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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\GenericOAuth2ResourceOwner;

/**
 * SlackResourceOwner
 *
 * @author Baptiste Clavié <clavie.b@gmail.com>
 */
class SlackResourceOwner extends GenericOAuth2ResourceOwner
{
    protected $paths = array(
        'identifier' => 'user_id',
        'nickname' => 'user',
    );

    /**
     * {@inheritDoc}
     */
    protected function configureOptions(OptionsResolverInterface $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'authorization_url' => 'https://slack.com/oauth/authorize',
            'infos_url' => 'https://slack.com/api/auth.test',
            'access_token_url' => 'https://slack.com/api/oauth.access',

            'scope' => 'identify',

            'use_bearer_authorization' => false,
            'attr_name' => 'token'
        ));
    }
}

