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
 * @author Joseph Bielawski <stloyd@gmail.com>
 */
final class StackExchangeResourceOwner extends GenericOAuth2ResourceOwner
{
    public const TYPE = 'stack_exchange';

    /**
     * {@inheritdoc}
     */
    protected array $paths = [
        'identifier' => 'items.0.user_id',
        'nickname' => 'items.0.display_name',
        'realname' => 'items.0.display_name',
        'profilepicture' => 'items.0.profile_image',
    ];

    /**
     * {@inheritdoc}
     */
    public function getUserInformation(array $accessToken, array $extraParameters = [])
    {
        $parameters = array_merge(
            [$this->options['attr_name'] => $accessToken['access_token']],
            ['site' => $this->options['site'], 'key' => $this->options['key']],
            $extraParameters
        );

        try {
            $content = $this->doGetUserInformationRequest($this->normalizeUrl($this->options['infos_url'], $parameters));

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

        $resolver->setRequired([
            'key',
        ]);

        $resolver->setDefaults([
            'authorization_url' => 'https://stackexchange.com/oauth',
            'access_token_url' => 'https://stackexchange.com/oauth/access_token',
            'infos_url' => 'https://api.stackexchange.com/2.0/me',

            'scope' => 'no_expiry',
            'site' => 'stackoverflow',
            'use_bearer_authorization' => false,
        ]);
    }
}
