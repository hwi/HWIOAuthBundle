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

use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Resource owner for the fiware keyrock idm oauth 2.0 service
 * more infos at https://github.com/ging/fi-ware-idm/wiki/Using-the-FIWARE-LAB-instance.
 *
 * @author Christian Kaspar <christian@sponsoo.de>
 */
final class FiwareResourceOwner extends GenericOAuth2ResourceOwner
{
    public const TYPE = 'fiware';

    /**
     * {@inheritdoc}
     */
    protected array $paths = [
        'identifier' => 'id',
        'nickname' => 'nickName',
        'realname' => 'displayName',
        'email' => 'email',
    ];

    /**
     * {@inheritdoc}
     */
    public function getAccessToken(Request $request, $redirectUri, array $extraParameters = [])
    {
        $parameters = array_merge([
            'code' => $request->query->get('code'),
            'grant_type' => 'authorization_code',
            'redirect_uri' => $redirectUri,
        ], $extraParameters);

        $headers = [
            'Authorization' => 'Basic '.base64_encode($this->options['client_id'].':'.$this->options['client_secret']),
        ];

        $response = $this->httpRequest($this->options['access_token_url'], http_build_query($parameters, '', '&'), $headers, 'POST');
        $responseContent = $this->getResponseContent($response);

        $this->validateResponseContent($responseContent);

        return $responseContent;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserInformation(array $accessToken, array $extraParameters = [])
    {
        if ($this->options['use_bearer_authorization']) {
            $content = $this->httpRequest(
                $this->normalizeUrl(
                    $this->options['infos_url'],
                    ['access_token' => $accessToken['access_token']]
                ),
                null,
                ['Authorization' => 'Bearer']
            );
        } else {
            $content = $this->doGetUserInformationRequest(
                $this->normalizeUrl(
                    $this->options['infos_url'],
                    [$this->options['attr_name'] => $accessToken['access_token']]
                )
            );
        }

        $response = $this->getUserResponse();
        $response->setData($content->getContent());
        $response->setResourceOwner($this);
        $response->setOAuthToken(new OAuthToken($accessToken));

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'authorization_url' => '{base_url}/oauth2/authorize',
            'access_token_url' => '{base_url}/oauth2/token',
            'revoke_token_url' => '{base_url}/oauth2/revoke',
            'infos_url' => '{base_url}/user',
        ]);

        $resolver->setRequired([
            'base_url',
        ]);

        $normalizer = function (Options $options, $value) {
            return str_replace('{base_url}', $options['base_url'], $value);
        };

        $resolver
            ->setNormalizer('authorization_url', $normalizer)
            ->setNormalizer('access_token_url', $normalizer)
            ->setNormalizer('revoke_token_url', $normalizer)
            ->setNormalizer('infos_url', $normalizer)
        ;
    }
}
