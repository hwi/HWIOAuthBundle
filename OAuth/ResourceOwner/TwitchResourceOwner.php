<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
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
    protected $paths = [
        'identifier' => '_id',
        'nickname' => 'display_name',
        'realname' => 'name',
        'email' => 'email',
        'profilepicture' => 'logo',
    ];

    /**
     * {@inheritdoc}
     */
    protected function doGetUserInformationRequest($url, array $parameters = [])
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

        $resolver->setDefaults([
            'authorization_url' => 'https://id.twitch.tv/oauth2/authorize',
            'access_token_url' => 'https://id.twitch.tv/oauth2/token',
            'infos_url' => 'https://api.twitch.tv/helix/users',
            'use_bearer_authorization' => false,
            'use_authorization_to_get_token' => false,
        ]);
    }
}
