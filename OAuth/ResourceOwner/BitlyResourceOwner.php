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
 * BitlyResourceOwner
 *
 * @author Joseph Bielawski <stloyd@gmail.com>
 */
class BitlyResourceOwner extends GenericOAuth2ResourceOwner
{
    /**
     * {@inheritDoc}
     */
    protected $options = array(
        'authorization_url'   => 'https://bitly.com/oauth/authorize',
        'access_token_url'    => 'https://api-ssl.bitly.com/oauth/access_token',
        'infos_url'           => 'https://api-ssl.bitly.com/v3/user/info?format=json',
    );

    /**
     * {@inheritDoc}
     */
    protected $paths = array(
        'identifier'     => 'data.login',
        'nickname'       => 'data.display_name',
        'realname'       => 'data.full_name',
        'profilepicture' => 'data.profile_image',
    );
}
