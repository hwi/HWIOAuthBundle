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
 * GoogleResourceOwner
 *
 * @author Geoffrey Bachelet <geoffrey.bachelet@gmail.com>
 * @author Alexander <iam.asm89@gmail.com>
 */
class GoogleResourceOwner extends GenericOAuth2ResourceOwner
{
    /**
     * {@inheritDoc}
     */
    protected $options = array(
        'authorization_url'   => 'https://accounts.google.com/o/oauth2/auth',
        'access_token_url'    => 'https://accounts.google.com/o/oauth2/token',
        'infos_url'           => 'https://www.googleapis.com/oauth2/v1/userinfo',

        'scope'               => 'https://www.googleapis.com/auth/userinfo.profile',
    );

    /**
     * {@inheritDoc}
     */
    protected $paths = array(
        'identifier'     => 'id',
        'nickname'       => 'name',
        'realname'       => 'name',
        'email'          => 'email',
        'profilepicture' => 'picture',
    );
}
