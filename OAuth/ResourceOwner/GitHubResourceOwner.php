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
    );

    /**
     * {@inheritDoc}
     */
    protected $paths = array(
        'identifier'     => 'id',
        'nickname'       => 'login',
        'realname'       => 'name',
        'email'          => 'email',
        'profilepicture' => 'avatar_url',
    );

    /**
     * Github unfortunately breaks the spec by using commas instead of spaces
     * to separate scopes
     */
    public function configure()
    {
        if (isset($this->options['scope'])) {
            $this->options['scope'] = str_replace(',', ' ', $this->options['scope']);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function revokeToken($token)
    {
        $parameters = array(
            'client_id'     => $this->getOption('client_id'),
            'client_secret' => $this->getOption('client_secret'),
        );

        /* @var $response \Buzz\Message\Response */
        $response = $this->httpRequest(sprintf('https://api.github.com/applications/%s/tokens/%s', $this->getOption('client_id'), $token), $parameters);
        if (404 === $response->getStatusCode()) {
            return false;
        }

        $response = $this->getResponseContent($response);
        $response = $this->httpRequest(sprintf('https://api.github.com/authorizations/%s', $response['id']), $parameters, array(), 'DELETE');

        return 204 === $response->getStatusCode();
    }
}
