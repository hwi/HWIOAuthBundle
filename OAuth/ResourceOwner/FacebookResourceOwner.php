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

use Symfony\Component\HttpFoundation\Request;

/**
 * FacebookResourceOwner
 *
 * @author Geoffrey Bachelet <geoffrey.bachelet@gmail.com>
 */
class FacebookResourceOwner extends GenericOAuth2ResourceOwner
{
    /**
     * {@inheritDoc}
     */
    protected $options = array(
        'authorization_url' => 'https://www.facebook.com/dialog/oauth',
        'access_token_url'  => 'https://graph.facebook.com/oauth/access_token',
        'revoke_token_url'  => 'https://graph.facebook.com/me/permissions',
        'infos_url'         => 'https://graph.facebook.com/me',

        // @link https://developers.facebook.com/docs/reference/dialogs/#display
        'display'           => null, 
    );

    /**
     * {@inheritDoc}
     */
    protected $paths = array(
        'identifier' => 'id',
        'nickname'   => 'username',
        'realname'   => 'name',
        'email'      => 'email',
    );

    /**
     * Facebook unfortunately breaks the spec by using commas instead of spaces
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
    public function getAuthorizationUrl($redirectUri, array $extraParameters = array())
    {
        return parent::getAuthorizationUrl($redirectUri, array_merge(array('display' => $this->getOption('display')), $extraParameters));
    }

    /**
     * Facebook unfortunately breaks the spec by using 'expires' instead of 'expires_in'
     */
    public function getAccessToken(Request $request, $redirectUri, array $extraParameters = array())
    {
        $accessToken = parent::getAccessToken($request, $redirectUri, $extraParameters);

        if (isset($accessToken['expires'])) {
            $accessToken['expires_in'] = $accessToken['expires'];
            unset($accessToken['expires']);
        }

        return $accessToken;
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

        $response = $this->httpRequest($this->normalizeUrl($this->getOption('revoke_token_url'), array('token' => $token)), $parameters, array(), 'POST');
        $response = $this->getResponseContent($response);

        return 'true' == $response;
    }
}
