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

use HWI\Bundle\OAuthBundle\Security\OAuthErrorHandler;
use Symfony\Component\HttpFoundation\Request as HttpRequest;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Jean-Christophe Cuvelier <jcc@atomseeds.com>
 * @author Fabiano Roberto <fabiano.roberto@ped.technology>
 */
final class InstagramResourceOwner extends GenericOAuth2ResourceOwner
{
    public const TYPE = 'instagram';

    /**
     * {@inheritdoc}
     */
    protected array $paths = [
        'identifier' => 'id',
        'nickname' => 'username',
        'realname' => 'username',
        'email' => 'id',
        'accounttype' => 'account_type',
    ];

    /**
     * {@inheritdoc}
     */
    public function getAccessToken(HttpRequest $request, $redirectUri, array $extraParameters = [])
    {
        OAuthErrorHandler::handleOAuthError($request);

        $parameters = array_merge([
            'code' => $request->query->get('code'),
            'grant_type' => 'authorization_code',
            'client_id' => $this->options['client_id'],
            'client_secret' => $this->options['client_secret'],
            'redirect_uri' => $redirectUri,
        ], $extraParameters);

        $response = $this->doGetTokenRequest($this->options['access_token_url'], $parameters);
        $response = $this->getResponseContent($response);

        $this->validateResponseContent($response);

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    protected function doGetUserInformationRequest($url, array $parameters = [])
    {
        return $this->httpRequest($this->normalizeUrl($url, $parameters), null, [], 'GET');
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'authorization_url' => 'https://api.instagram.com/oauth/authorize',
            'access_token_url' => 'https://api.instagram.com/oauth/access_token',
            'infos_url' => 'https://api.instagram.com/v1/users/self',
            'use_bearer_authorization' => false,
        ]);
    }
}
