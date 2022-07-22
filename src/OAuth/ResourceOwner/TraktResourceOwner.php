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
 * @author Julien DIDIER <julien@didier.io>
 */
final class TraktResourceOwner extends GenericOAuth2ResourceOwner
{
    public const TYPE = 'trakt';

    /**
     * {@inheritdoc}
     */
    protected array $paths = [
        'identifier' => 'username',
        'nickname' => 'username',
        'realname' => 'name',
        'profilepicture' => 'images.avatar.full',
    ];

    /**
     * {@inheritdoc}
     */
    public function getUserInformation(array $accessToken, array $extraParameters = [])
    {
        $content = $this->httpRequest($this->normalizeUrl($this->options['infos_url']), null, [
            'Authorization' => 'Bearer '.$accessToken['access_token'],
            'Content-Type' => 'application/json',
            'trakt-api-key' => $this->options['client_id'],
            'trakt-api-version' => 2,
        ]);

        try {
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
            'authorization_url' => 'https://api-v2launch.trakt.tv/oauth/authorize',
            'access_token_url' => 'https://api-v2launch.trakt.tv/oauth/token',
            'infos_url' => 'https://api-v2launch.trakt.tv/users/me?extended=images',
        ]);
    }
}
