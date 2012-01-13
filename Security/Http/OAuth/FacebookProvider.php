<?php

/*
 * This file is part of the KnpOAuthBundle package.
 *
 * (c) KnpLabs <hello@knplabs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Knp\Bundle\OAuthBundle\Security\Http\OAuth;

use Knp\Bundle\OAuthBundle\Security\Http\OAuth\OAuthProvider;

/**
 * FacebookProvider
 *
 * @author Geoffrey Bachelet <geoffrey.bachelet@gmail.com>
 */
class FacebookProvider extends OAuthProvider
{
    /**
     * {@inheritDoc}
     */
    protected $options = array(
        'authorization_url' => 'https://www.facebook.com/dialog/oauth',
        'access_token_url'  => 'https://graph.facebook.com/oauth/access_token',
        'infos_url'         => 'https://graph.facebook.com/me',
        'username_path'     => 'name',
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