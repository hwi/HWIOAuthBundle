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

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * YandexResourceOwner.
 *
 * @author Anton Kamenschikov <wiistriker [at] gmail.com>
 */
class YandexResourceOwner extends GenericOAuth2ResourceOwner
{
    /**
     * {@inheritdoc}
     */
    protected $paths = array(
        'identifier' => 'id',
        'nickname' => 'display_name',
        'realname' => 'real_name',
        'email' => 'default_email',
    );

    /**
     * {@inheritdoc}
     */
    protected function doGetUserInformationRequest($url, array $parameters = array())
    {
        // Yandex require to pass the OAuth token as 'oauth_token' instead of 'access_token'
        return $this->httpRequest(str_replace('access_token', 'oauth_token', $url));
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'authorization_url' => 'https://oauth.yandex.ru/authorize',
            'access_token_url' => 'https://oauth.yandex.ru/token',
            'infos_url' => 'https://login.yandex.ru/info?format=json',
        ));
    }
}
