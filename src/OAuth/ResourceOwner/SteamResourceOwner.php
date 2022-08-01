<?php

namespace HWI\Bundle\OAuthBundle\OAuth\ResourceOwner;

use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SteamResourceOwner extends GenericOpenId2ResourceOwner
{
    public const TYPE = 'steam';

    /**
     * {@inheritdoc}
     */
    protected array $paths = [
        'identifier'     => 'response.players.0.steamid',
        'nickname'       => 'response.players.0.personaname',
        'profilepicture' => 'response.players.0.avatarmedium',
    ];

    public function getUserInformation(array $accessToken, array $extraParameters = []): UserResponseInterface
    {
        $extraParameters = ['key' => $this->options['client_secret']];

        return parent::getUserInformation($accessToken, $extraParameters);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'authorization_url' => 'https://steamcommunity.com/openid/id',
            'access_token_url'  => 'https://steamcommunity.com/openid/id',
            'infos_url'         => 'https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/',
            'attr_name'         => 'steamids',
        ]);
    }

    protected function parseUserIdFromIdentity(string $identity): string
    {
        preg_match('/7[0-9]{15,25}/', $identity, $matches);

        return (string) $matches[0];
    }
}
