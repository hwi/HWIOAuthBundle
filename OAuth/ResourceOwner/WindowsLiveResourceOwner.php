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
 * WindowsLiveResourceOwner
 *
 * @author Alexander <alexander@hardware.info>
 */
class WindowsLiveResourceOwner extends GenericOAuth2ResourceOwner
{
    /**
     * {@inheritDoc}
     */
    protected $options = array(
        'authorization_url'   => 'https://login.live.com/oauth20_authorize.srf',
        'access_token_url'    => 'https://login.live.com/oauth20_token.srf',
        'infos_url'           => 'https://apis.live.net/v5.0/me',
        'scope'               => '',
        'user_response_class' => '\HWI\Bundle\OAuthBundle\OAuth\Response\PathUserResponse',
    );

    /**
     * {@inheritDoc}
     */
    protected $paths = array(
        'username'     => 'id',
        'displayname'  => 'name',
    );
}
