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
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use Symfony\Component\HttpClient\Exception\JsonException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * @author Jamie Sutherland<me@jamiesutherland.com>
 */
final class DropboxResourceOwner extends GenericOAuth2ResourceOwner
{
    public const TYPE = 'dropbox';

    /**
     * {@inheritdoc}
     */
    protected array $paths = [
        'identifier' => 'account_id',
        'nickname' => 'email',
        'realname' => 'email',
        'email' => 'email',
    ];

    /**
     * Dropbox API v2 requires a POST request to simply get user info!
     *
     * @return UserResponseInterface
     */
    public function getUserInformation(array $accessToken,
        array $extraParameters = []
    ) {
        if ($this->options['use_bearer_authorization']) {
            $content = $this->httpRequest(
                $this->normalizeUrl($this->options['infos_url'], $extraParameters),
                'null',
                [
                    'Authorization' => 'Bearer '.$accessToken['access_token'],
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json; charset=utf-8',
                ], 'POST');
        } else {
            $content = $this->doGetUserInformationRequest(
                $this->normalizeUrl(
                    $this->options['infos_url'],
                    array_merge([$this->options['attr_name'] => $accessToken['access_token']], $extraParameters)
                )
            );
        }

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
            'authorization_url' => 'https://www.dropbox.com/oauth2/authorize',
            'access_token_url' => 'https://api.dropbox.com/oauth2/token',
            'infos_url' => 'https://api.dropboxapi.com/2/users/get_current_account',
        ]);
    }
}
