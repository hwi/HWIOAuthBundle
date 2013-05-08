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
 * DisqusResourceOwner
 *
 * @author Alexander Müller <amr@kapthon.com>
 */
class DisqusResourceOwner extends GenericOAuth2ResourceOwner
{
    /**
     * {@inheritDoc}
     */
    protected $options = array(
        'authorization_url'   => 'https://disqus.com/api/oauth/2.0/authorize/',
        'access_token_url'    => 'https://disqus.com/api/oauth/2.0/access_token/',
        'infos_url'           => 'https://disqus.com/api/3.0/users/details.json',
        'scope'               => 'read',
        'user_response_class' => '\HWI\Bundle\OAuthBundle\OAuth\Response\PathUserResponse',
    );

    /**
     * {@inheritDoc}
     */
    protected $paths = array(
        'identifier' => 'response.id',
        'nickname'   => 'response.username',
        'realname'   => 'response.name',
    );

    /**
     * DISQUS unfortunately breaks the spec by using commas instead of spaces
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
    protected function doGetUserInformationRequest($url, array $parameters = array())
    {
        /* DISQUS requires api key and secret for user information requests */
        $url = $this->normalizeUrl($url, array(
            'api_key'    => $this->getOption('client_id'),
            'api_secret' => $this->getOption('client_secret'),
        ));

        return parent::doGetUserInformationRequest($url, $parameters);
    }
}
