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
 * InstagramResourceOwner
 *
 * @author Jean-Christophe Cuvelier <jcc@atomseeds.com>
 */
class InstagramResourceOwner extends GenericOAuth2ResourceOwner
{
    /**
     * {@inheritDoc}
     */
    protected $options = array(
        'authorization_url' => 'https://api.instagram.com/oauth/authorize',
        'access_token_url'  => 'https://api.instagram.com/oauth/access_token',
        'revoke_token_url'  => 'https://instagram.com/accounts/manage_access',
        'infos_url'         => 'https://api.instagram.com/v1/users/self',

    );

    /**
     * {@inheritDoc}
     */
    protected $paths = array(
        'identifier' => 'data.id',
        'nickname'   => 'data.username',
        'realname'   => 'data.full_name',
        'profilepicture'   => 'data.profile_picture',
    );

}
