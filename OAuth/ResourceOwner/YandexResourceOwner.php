<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\OAuth\ResourceOwner;

/**
 * YandexResourceOwner
 *
 * @author Anton Kamenschikov <wiistriker [at] gmail.com>
 */
class YandexResourceOwner extends GenericOAuth2ResourceOwner
{
    /**
     * {@inheritDoc}
     */
    protected $options = array(
        'authorization_url'   => 'https://oauth.yandex.ru/authorize',
        'access_token_url'    => 'https://oauth.yandex.ru/token',
        'infos_url'           => 'https://login.yandex.ru/info?format=json',
        'scope'               => null,
        'user_response_class' => '\HWI\Bundle\OAuthBundle\OAuth\Response\PathUserResponse',
    );

    /**
     * {@inheritDoc}
     */
    protected $paths = array(
        'identifier' => 'id',
        'nickname'   => 'display_name',
        'realname'   => 'real_name',
    );

    /**
     * {@inheritDoc}
     */
    public function getUserInformation($accessToken)
    {
        $url = $this->normalizeUrl($this->getOption('infos_url'), array(
            'oauth_token' => $accessToken
        ));

        $content = $this->doGetUserInformationRequest($url)->getContent();

        $response = $this->getUserResponse();
        $response->setResponse($content);
        $response->setResourceOwner($this);
        $response->setAccessToken($accessToken);

        return $response;
    }
}