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
 * DisqusResourceOwner.
 *
 * @author Alexander Müller <amr@kapthon.com>
 */
class DisqusResourceOwner extends GenericOAuth2ResourceOwner
{
    /**
     * {@inheritdoc}
     */
    protected $paths = array(
        'identifier' => 'response.id',
        'nickname' => 'response.username',
        'realname' => 'response.name',
    );

    /**
     * {@inheritdoc}
     */
    protected function doGetUserInformationRequest($url, array $parameters = array())
    {
        // Disqus requires api key and secret for user information requests
        $url = $this->normalizeUrl($url, array(
            'api_key' => $this->options['client_id'],
            'api_secret' => $this->options['client_secret'],
        ));

        return parent::doGetUserInformationRequest($url, $parameters);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'authorization_url' => 'https://disqus.com/api/oauth/2.0/authorize/',
            'access_token_url' => 'https://disqus.com/api/oauth/2.0/access_token/',
            'infos_url' => 'https://disqus.com/api/3.0/users/details.json',

            'scope' => 'read',

            'use_commas_in_scope' => true,
        ));
    }
}
