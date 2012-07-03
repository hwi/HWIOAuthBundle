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
 * FlickrResourceOwner
 *
 * @author Karel <karel@hardware.info>
 * @author Joseph Bielawski <stloyd@gmail.com>
 */
class FlickrResourceOwner extends GenericOAuth1ResourceOwner
{
    /**
     * {@inheritDoc}
     */
    protected $options = array(
        'authorization_url'   => 'http://www.flickr.com/services/oauth/authorize',
        'request_token_url'   => 'http://www.flickr.com/services/oauth/request_token',
        'access_token_url'    => 'http://www.flickr.com/services/oauth/access_token',
        'infos_url'           => 'http://api.flickr.com/services/rest',
        'user_response_class' => '\HWI\Bundle\OAuthBundle\OAuth\Response\PathUserResponse',
        'realm'               => null,
        'perms'               => 'read',
    );

    /**
     * {@inheritDoc}
     */
    protected $paths = array(
        'username'     => 'username',
        'displayname'  => 'fullname',
    );

    /**
     * {@inheritDoc}
     */
    public function getAuthorizationUrl($redirectUri, array $extraParameters = array())
    {
        $token = $this->getRequestToken($redirectUri, $extraParameters);

        return $this->getOption('authorization_url').'?'.http_build_query(array(
            'oauth_token' => $token['oauth_token'],
            'perms'       => $this->getOption('perms')
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function getUserInformation($accessToken)
    {
        // We have user info from Flickr with access token, lets fetch it, and re-use
        if (null === $content = $this->storage->fetch($this, $accessToken['oauth_token'])) {
            throw new \RuntimeException('No request token found in the storage.');
        }

        $response = $this->getUserResponse();
        $response->setResponse(json_encode(array(
            'username' => $content['username'],
            'fullname' => $content['fullname'],
        )));
        $response->setResourceOwner($this);

        return $response;
    }

    /**
     * {@inheritDoc}
     */
    public function getAccessToken(Request $request, $redirectUri, array $extraParameters = array())
    {
        $response = parent::getAccessToken($request, $redirectUri, $extraParameters);

        // We have user info from Flickr with access token, lets save it
        $this->storage->save($this, $response);

        return $response;
    }

    /**
     * {@inheritDoc}
     */
    protected function doGetAccessTokenRequest($url, array $parameters = array())
    {
        $url .= '?'.http_build_query($parameters);

        return $this->httpRequest($url, null, array(), array(), 'GET');
    }

    /**
     * {@inheritDoc}
     */
    protected function doGetRequestTokenRequest($url, array $parameters = array())
    {
        $url .= '?'.http_build_query($parameters);

        return $this->httpRequest($url, null, array(), array(), 'GET');
    }

    /**
     * {@inheritDoc}
     */
    protected function signRequest($method, $url, $parameters, $tokenSecret = '')
    {
        return parent::signRequest('GET', $url, $parameters, $tokenSecret);
    }
}
