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
    );

    /**
     * {@inheritDoc}
     */
    protected $paths = array(
        'identifier' => 'id',
        'nickname'   => 'display_name',
        'realname'   => 'real_name',
    );
}
