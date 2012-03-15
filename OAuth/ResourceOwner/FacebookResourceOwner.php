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
 * FacebookResourceOwner
 *
 * @author Geoffrey Bachelet <geoffrey.bachelet@gmail.com>
 */
class FacebookResourceOwner extends GenericResourceOwner
{
    /**
     * {@inheritDoc}
     */
    protected $options = array(
        'authorization_url'   => 'https://www.facebook.com/dialog/oauth',
        'access_token_url'    => 'https://graph.facebook.com/oauth/access_token',
        'infos_url'           => 'https://graph.facebook.com/me',
        'user_response_class' => '\HWI\Bundle\OAuthBundle\OAuth\Response\PathUserResponse',
    );

    /**
     * {@inheritDoc}
     */
    protected $paths = array(
        'username'     => 'id',
        'displayname'  => 'name',
    );

    /**
     * Facebook unfortunately breaks the spec by using commas instead of spaces
     * to separate scopes
     */
    public function configure()
    {
        $this->options['scope'] = str_replace(',', ' ', $this->options['scope']);
    }
}
