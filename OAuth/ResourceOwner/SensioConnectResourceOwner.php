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

use Symfony\Component\Security\Core\Exception\AuthenticationException,
    Symfony\Component\Security\Http\HttpUtils;

/**
 * SensioResourceOwner
 *
 * @author Joseph Bielawski <stloyd@gmail.com>
 */
class SensioConnectResourceOwner extends GenericResourceOwner
{
    /**
     * {@inheritDoc}
     */
    protected $options = array(
        'authorization_url'   => 'https://connect.sensiolabs.com/oauth/authorize',
        'access_token_url'    => 'https://connect.sensiolabs.com/oauth/access_token',
        'infos_url'           => 'https://connect.sensiolabs.com/api',
        'scope'               => '',
        'user_response_class' => '\HWI\Bundle\OAuthBundle\OAuth\Response\SensioConnectUserResponse',
        'response_type'       => 'code',
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
     * {@inheritDoc}
     */
    public function getAccessToken($code, $redirectUri, array $extraParameters = array())
    {
        $parameters = array_merge($extraParameters, array(
            'code'          => $code,
            'grant_type'    => 'authorization_code',
            'client_id'     => $this->getOption('client_id'),
            'client_secret' => $this->getOption('client_secret'),
            'redirect_uri'  => $redirectUri,
            'response_type' => 'code',
            'scope'         => $this->getOption('scope'),
        ));

        $response = $this->httpRequest($this->getOption('access_token_url'), $parameters, 'POST');
        $response = json_decode($response->getContent(), true);

        if (isset($response['error'])) {
            throw new AuthenticationException(sprintf('OAuth error: "%s" with message: "%s"', $response['error'], $response['message']));
        }

        if (!isset($response['access_token'])) {
            throw new AuthenticationException('Not a valid access token.');
        }

        return $response['access_token'];
    }

    /**
     * {@inheritDoc}
     */
    public function getUserInformation($accessToken)
    {
        $url  = $this->getOption('infos_url');
        $url .= (false !== strpos($url, '?') ? '&' : '?').http_build_query(array(
            'access_token' => $accessToken
        ));

        $content = $this->httpRequest($url, null, null, array('Accept: application/vnd.com.sensiolabs.connect+xml'))->getContent();

        $response = $this->getUserResponse();
        $response->setResponse($content);
        $response->setResourceOwner($this);

        return $response;
    }
}
