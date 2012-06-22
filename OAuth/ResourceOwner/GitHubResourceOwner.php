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
 * GitHubResourceOwner
 *
 * @author Geoffrey Bachelet <geoffrey.bachelet@gmail.com>
 * @author Alexander <iam.asm89@gmail.com>
 */
class GitHubResourceOwner extends GenericOAuth2ResourceOwner
{
    /**
     * {@inheritDoc}
     */
    protected $options = array(
        'authorization_url'   => 'https://github.com/login/oauth/authorize',
        'access_token_url'    => 'https://github.com/login/oauth/access_token',
        'infos_url'           => 'https://api.github.com/user',
        'scope'               => '',
        'user_response_class' => '\HWI\Bundle\OAuthBundle\OAuth\Response\AdvancedPathUserResponse',
    );

    /**
     * {@inheritDoc}
     */
    protected $paths = array(
        'username'       => 'login',
        'displayname'    => 'name',
        'email'          => 'email',
        'profilepicture' => 'avatar_url',
    );

    /**
     * Github unfortunately breaks the spec by using commas instead of spaces
     * to separate scopes
     */
    public function configure()
    {
        $this->options['scope'] = str_replace(',', ' ', $this->options['scope']);
    }
}
