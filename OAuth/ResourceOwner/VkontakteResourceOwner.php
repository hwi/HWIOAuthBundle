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
 * VkontakteResourceOwner
 *
 * @author Adrov Igor <nucleartux@gmail.com>
 */
class VkontakteResourceOwner extends GenericOAuth2ResourceOwner
{
    /**
     * {@inheritDoc}
     */
    protected $options = array(
        'authorization_url'   => 'https://api.vk.com/oauth/authorize',
        'access_token_url'    => 'https://oauth.vk.com/access_token',
        'infos_url'           => 'https://api.vk.com/method/getUserInfoEx',
        'scope'               => '',
        'user_response_class' => '\HWI\Bundle\OAuthBundle\OAuth\Response\PathUserResponse',
    );

    /**
     * {@inheritDoc}
     */
    protected $paths = array(
        'username'     => 'response.user_id',
        'displayname'  => 'response.user_name',
    );

    /**
     * Vkontakte unfortunately breaks the spec by using commas instead of spaces
     * to separate scopes
     */
    public function configure()
    {
        $this->options['scope'] = str_replace(',', ' ', $this->options['scope']);
    }
}
