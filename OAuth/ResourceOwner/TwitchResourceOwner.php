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
 * TwitchResourceOwner.
 *
 * @author Simon Bräuer <redshark1802>
 */
class TwitchResourceOwner extends GenericOAuth2ResourceOwner
{
    /**
     * {@inheritdoc}
     */
    protected $paths = array(
        'identifier' => '_id',
        'nickname' => 'display_name',
        'realname' => 'name',
        'email' => 'email',
        'profilepicture' => 'logo',
    );

    /**
     * {@inheritdoc}
     */
    protected function doGetTokenRequest($url, array $parameters = array())
    {
        return $this->httpRequest($url, http_build_query($parameters, '', '&'), array(), 'POST');
    }

    /**
     * {@inheritdoc}
     */
    protected function doGetUserInformationRequest($url, array $parameters = array())
    {
        // Twitch require to pass the OAuth token as 'oauth_token' instead of 'access_token'
        return parent::doGetUserInformationRequest(str_replace('access_token', 'oauth_token', $url), $parameters);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'authorization_url' => 'https://api.twitch.tv/kraken/oauth2/authorize',
            'access_token_url' => 'https://api.twitch.tv/kraken/oauth2/token',
            'infos_url' => 'https://api.twitch.tv/kraken/user',
            'use_bearer_authorization' => false,
        ));
    }
}
