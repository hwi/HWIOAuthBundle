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
 * @author Simon Bräuer <redshark1802>
 */
final class TwitchResourceOwner extends GenericOAuth2ResourceOwner
{
    public const TYPE = 'twitch';

    /**
     * {@inheritdoc}
     */
    protected array $paths = [
        'identifier' => 'data.0.id',
        'nickname' => 'data.0.login',
        'realname' => 'data.0.display_name',
        'email' => 'data.0.email', // Require scope "user:read:email"
        'profilepicture' => 'data.0.profile_image_url',
    ];

    /**
     * {@inheritdoc}
     */
    protected function httpRequest($url, $content = null, array $headers = [], $method = null)
    {
        // Twitch also require that you provide the client id as a header
        $headers += ['Client-ID' => $this->options['client_id']];

        return parent::httpRequest($url, $content, $headers, $method);
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
            'use_bearer_authorization' => true,
            'use_authorization_to_get_token' => false,
        ]);
    }
}
