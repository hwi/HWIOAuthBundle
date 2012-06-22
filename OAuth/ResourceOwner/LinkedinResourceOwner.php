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
 * LinkedinResourceOwner
 *
 * @author Francisco Facioni <fran6co@gmail.com>
 */
class LinkedinResourceOwner extends GenericOAuth1ResourceOwner
{
    /**
     * {@inheritDoc}
     */
    protected $options = array(
        'authorization_url'   => 'https://www.linkedin.com/uas/oauth/authenticate',
        'request_token_url'   => 'https://api.linkedin.com/uas/oauth/requestToken',
        'access_token_url'    => 'https://api.linkedin.com/uas/oauth/accessToken',
        'infos_url'           => 'http://api.linkedin.com/v1/people/~',
        'scope'               => '',
        'user_response_class' => '\HWI\Bundle\OAuthBundle\OAuth\Response\PathUserResponse',
        'realm'               => 'http://api.linkedin.com',
    );

    /**
     * {@inheritDoc}
     */
    protected $paths = array(
        'username'     => 'id',
        'displayname'  => 'name',
    );
}
