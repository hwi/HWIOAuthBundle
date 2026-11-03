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
 * TikTok Login Kit: OAuth 2.0 with the client id sent as `client_key` and a user info
 * request that names the fields it wants, nested under `data.user` in the response.
 *
 * @see https://developers.tiktok.com/doc/login-kit-web
 *
 * @author Thomas Jarrand <thomas.jarrand@gmail.com>
 */
final class TikTokResourceOwner extends GenericOAuth2ResourceOwner
{
    public const TYPE = 'tiktok';

    /**
     * {@inheritdoc}
     */
    protected array $paths = [
        'identifier' => 'data.user.open_id',
        'nickname' => 'data.user.display_name',
        'realname' => 'data.user.display_name',
        'profilepicture' => 'data.user.avatar_url',
    ];

    /**
     * {@inheritdoc}
     */
    public function getAuthorizationUrl($redirectUri, array $extraParameters = [])
    {
        if ($this->options['csrf']) {
            $this->handleCsrfToken();
        }

        $parameters = array_merge([
            'response_type' => 'code',
            'client_key' => $this->options['client_id'],
            'scope' => $this->options['scope'],
            'state' => $this->state->encode(),
            'redirect_uri' => $redirectUri,
        ], $extraParameters);

        return $this->normalizeUrl($this->options['authorization_url'], $parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function getUserInformation(array $accessToken, array $extraParameters = [])
    {
        return parent::getUserInformation($accessToken, array_merge(['fields' => $this->options['fields']], $extraParameters));
    }

    /**
     * {@inheritdoc}
     */
    public function revokeToken($token)
    {
        $parameters = [
            'client_key' => $this->options['client_id'],
            'client_secret' => $this->options['client_secret'],
            'token' => $token,
        ];

        $response = $this->httpRequest($this->options['revoke_token_url'], http_build_query($parameters, '', '&'));

        return 200 === $response->getStatusCode();
    }

    /**
     * {@inheritdoc}
     */
    protected function doGetTokenRequest($url, array $parameters = [])
    {
        unset($parameters['client_id']);

        $parameters['client_key'] = $this->options['client_id'];
        $parameters['client_secret'] = $this->options['client_secret'];

        return $this->httpRequest($url, http_build_query($parameters, '', '&'));
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'authorization_url' => 'https://www.tiktok.com/v2/auth/authorize/',
            'access_token_url' => 'https://open.tiktokapis.com/v2/oauth/token/',
            'revoke_token_url' => 'https://open.tiktokapis.com/v2/oauth/revoke/',
            'infos_url' => 'https://open.tiktokapis.com/v2/user/info/',
            'scope' => 'user.info.basic',
            // The user info fields to request, which the granted scopes must cover
            'fields' => 'open_id,union_id,avatar_url,display_name',
        ]);
    }
}
