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
 * TwitterResourceOwner
 *
 * @author Alexander <iam.asm89@gmail.com>
 */
class TwitterResourceOwner extends GenericOAuth1ResourceOwner
{
    /**
     * {@inheritDoc}
     */
    protected $options = array(
        'authorization_url'   => 'https://api.twitter.com/oauth/authenticate',
        'request_token_url'   => 'https://api.twitter.com/oauth/request_token',
        'access_token_url'    => 'https://api.twitter.com/oauth/access_token',
        'infos_url'           => 'http://api.twitter.com/1.1/account/verify_credentials.json',
    );

    /**
     * {@inheritDoc}
     */
    protected $paths = array(
        'identifier' => 'id',
        'nickname'   => 'screen_name',
        'realname'   => 'name',
    );
}
