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
 * @author Nhung Le <lthnhung0626@gmail.com>
 */
final class DiscordResourceOwner extends GenericOAuth2ResourceOwner
{
    public const TYPE = 'discord';

    /**
     * {@inheritdoc}
     */
    protected array $paths = [
        'identifier' => 'id',
        'nickname' => 'username',
        'realname' => 'global_name',
        'email' => 'email',
        'profilepicture' => 'avatar_url',
    ];

    /**
     * {@inheritdoc}
     */
    public function getUserInformation(array $accessToken, array $extraParameters = [])
    {
        $response = parent::getUserInformation($accessToken, $extraParameters);

        $responseData = $response->getData();
        if (!empty($responseData['id']) && !empty($responseData['avatar'])) {
            // Discord returns the avatar as a hash, build the CDN url from it
            $responseData['avatar_url'] = sprintf(
                'https://cdn.discordapp.com/avatars/%s/%s.png',
                $responseData['id'],
                $responseData['avatar']
            );

            $response->setData($responseData);
        }

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'authorization_url' => 'https://discord.com/oauth2/authorize',
            'access_token_url' => 'https://discord.com/api/oauth2/token',
            'infos_url' => 'https://discord.com/api/v10/users/@me',
        ]);
    }
}
