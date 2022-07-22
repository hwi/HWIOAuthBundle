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
use HWI\Bundle\OAuthBundle\OAuth\Response\SensioConnectUserResponse;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use Symfony\Component\HttpClient\Exception\JsonException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * @author Joseph Bielawski <stloyd@gmail.com>
 */
final class SensioConnectResourceOwner extends GenericOAuth2ResourceOwner
{
    public const TYPE = 'sensio_connect';

    /**
     * {@inheritdoc}
     */
    public function getUserInformation(array $accessToken, array $extraParameters = [])
    {
        $content = $this->doGetUserInformationRequest(
            $this->normalizeUrl(
                $this->options['infos_url'],
                array_merge([$this->options['attr_name'] => $accessToken['access_token']], $extraParameters)
            )
        );

        try {
            $response = $this->getUserResponse();
            $response->setData($content->getContent(false));
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
    protected function doGetUserInformationRequest($url, array $parameters = [])
    {
        return $this->httpRequest($url, null, ['Accept' => 'application/vnd.com.sensiolabs.connect+xml']);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'authorization_url' => 'https://connect.symfony.com/oauth/authorize',
            'access_token_url' => 'https://connect.symfony.com/oauth/access_token',
            'infos_url' => 'https://connect.symfony.com/api',

            'user_response_class' => SensioConnectUserResponse::class,

            'response_type' => 'code',

            'use_bearer_authorization' => false,
            'csrf' => true,
        ]);
    }
}
