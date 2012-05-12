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
 * ViadeoResourceOwner
 *
 * @author Sullivan SENECHAL <soullivaneuh@gmail.com>
 */
class ViadeoResourceOwner extends GenericResourceOwner
{
    /**
     * {@inheritDoc}
     */
    protected $options = array(
        'authorization_url'   => 'https://secure.viadeo.com/oauth-provider/authorize2',
        'access_token_url'    => 'https://secure.viadeo.com/oauth-provider/access_token2',
        'infos_url'           => 'https://api.viadeo.com/me',
        'scope'               => '',
        'user_response_class' => '\HWI\Bundle\OAuthBundle\OAuth\Response\PathUserResponse',
        'access_token_encode' => 'json'
    );

    /**
     * {@inheritDoc}
     */
    protected $paths = array(
        'username'     => 'nickname',
        'displayname'  => 'name',
    );
}
