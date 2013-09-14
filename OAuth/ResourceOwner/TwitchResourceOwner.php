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
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;

/**
 * TwitchResourceOwner
 *
 * @author Simon Bräuer <redshark1802>
 */
class TwitchResourceOwner extends GenericOAuth2ResourceOwner
{
    /**
     * {@inheritDoc}
     */
    protected $options = array(
        'authorization_url'       => 'https://api.twitch.tv/kraken/oauth2/authorize',
        'access_token_url'        => 'https://api.twitch.tv/kraken/oauth2/token',
        'infos_url'               => 'https://api.twitch.tv/kraken/user',
    );

    /**
     * {@inheritDoc}
     */
    protected $paths = array(
        'identifier'     => '_id',
        'nickname'       => 'display_name',
        'realname'       => 'name',
        'email'          => 'email',
        'profilepicture' => 'logo',
    );

    /**
     * {@inheritDoc}
     */
    public function getAuthorizationUrl($redirectUri, array $extraParameters = array())
    {
        $parameters = array_merge(array(
            'response_type' => 'code',
            'client_id'     => $this->getOption('client_id'),
            'redirect_uri'  => $redirectUri,
            'scope'         => $this->getOption('scope'),

        ), $extraParameters);
        return parent::getAuthorizationUrl($redirectUri, $parameters);
    }

    /**
     * {@inheritDoc}
     */
    public function getAccessToken(Request $request, $redirectUri, array $extraParameters = array())
    {

        $parameters = array_merge(array(
            'client_id'     => $this->getOption('client_id'),
            'client_secret' => $this->getOption('client_secret'),
            'grant_type' => 'authorization_code',
            'redirect_uri'  => $redirectUri,
            'code'          => $request->query->get('code'),
        ), $extraParameters);

        return parent::getAccessToken($request, $redirectUri, $parameters);
    }

    /**
     * {@inheritDoc}
     */
    protected function doGetTokenRequest($url, array $parameters = array())
    {
        return $this->httpRequest($url, http_build_query($parameters, '', '&'), array(), 'POST');
    }

    /**
     * {@inheritDoc}
     */
    public function getUserInformation(array $accessToken, array $extraParameters = array())
    {

        $url = $this->normalizeUrl($this->getOption('infos_url'), array(
            'oauth_token' => $accessToken['access_token']
        ));

        $content = $this->doGetUserInformationRequest($url)->getContent();

        $response = $this->getUserResponse();
        $response->setResponse($content);
        $response->setResourceOwner($this);
        $response->setOAuthToken(new OAuthToken($accessToken));

        return $response;
    }
}
