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

use HWI\Bundle\OAuthBundle\OAuth\Exception\HttpTransportException;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use Symfony\Component\HttpClient\Exception\JsonException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * @author Janne Savolainen <janne.savolainen@sempre.fi>
 */
final class SpotifyResourceOwner extends GenericOAuth2ResourceOwner
{
    public const TYPE = 'spotify';

    /**
     * {@inheritdoc}
     */
    protected array $paths = [
        'identifier' => 'id',
        'nickname' => 'id',
        'realname' => 'display_name',
        'email' => 'email',
        'profilepicture' => 'images.0.url',
    ];

    /**
     * {@inheritdoc}
     */
    public function getUserInformation(array $accessToken = null, array $extraParameters = [])
    {
        $url = $this->normalizeUrl($this->options['infos_url'], [
            'access_token' => $accessToken['access_token'],
        ]);

        try {
            $content = $this->doGetUserInformationRequest($url);

            $response = $this->getUserResponse();
            $response->setData($content->toArray(false));
            $response->setResourceOwner($this);
            $response->setOAuthToken(new OAuthToken($accessToken));

            return $response;
        } catch (TransportExceptionInterface|JsonException $e) {
            throw new HttpTransportException('Error while sending HTTP request', $this->getName(), $e->getCode(), $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'authorization_url' => 'https://accounts.spotify.com/authorize',
            'access_token_url' => 'https://accounts.spotify.com/api/token',
            'infos_url' => 'https://api.spotify.com/v1/me',
        ]);
    }
}
