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

use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * GenericOAuth2ResourceOwner
 *
 * @author Geoffrey Bachelet <geoffrey.bachelet@gmail.com>
 * @author Alexander <iam.asm89@gmail.com>
 */
class GenericOAuth2ResourceOwner extends AbstractResourceOwner
{
    /**
     * @var array
     */
    protected $defaultOptions = array(
        'client_id'           => null,
        'client_secret'       => null,

        'infos_url'           => null,

        'user_response_class' => 'HWI\Bundle\OAuthBundle\OAuth\Response\PathUserResponse',

        'scope'               => null,

        'csrf'                => false,
    );

    /**
     * {@inheritDoc}
     */
    public function getUserInformation(array $accessToken, array $extraParameters = array())
    {
        return $this->getCustomInformation($accessToken, $this->getOption('infos_url'), $extraParameters);
    }

    /**
     * {@inheritDoc}
     */
    public function getCustomInformation(array $accessToken, $url, array $extraParameters = array())
    {
        $url = $this->normalizeUrl($url, array(
            'access_token' => $accessToken['access_token']
        ));

        $content = $this->doGetUserInformationRequest($url)->getContent();

        $response = $this->getUserResponse();
        $response->setResponse($content);
        $response->setResourceOwner($this);
        $response->setOAuthToken(new OAuthToken($accessToken));

        return $response;
    }

    /**
     * {@inheritDoc}
     */
    public function getAuthorizationUrl($redirectUri, array $extraParameters = array())
    {
        if ($this->getOption('csrf')) {
            if (null === $this->state) {
                $this->state = $this->generateNonce();
            }

            $this->storage->save($this, $this->state, 'csrf_state');
        }

        $parameters = array_merge(array(
            'response_type' => 'code',
            'client_id'     => $this->getOption('client_id'),
            'scope'         => $this->getOption('scope'),
            'state'         => $this->state ? urlencode($this->state) : null,
            'redirect_uri'  => $redirectUri,
        ), $extraParameters);

        return $this->normalizeUrl($this->getOption('authorization_url'), $parameters);
    }

    /**
     * Retrieve an access token for a given code.
     *
     * @param Request $request         The request object from where the code is going to extracted
     * @param mixed   $redirectUri     The uri to redirect the client back to
     * @param array   $extraParameters An array of parameters to add to the url
     *
     * @return array Array containing the access token and it's 'expires_in' value,
     *               along with any other parameters returned from the authentication
     *               provider.
     *
     * @throws AuthenticationException If an OAuth error occurred or no access token is found
     */
    public function getAccessToken(Request $request, $redirectUri, array $extraParameters = array())
    {
        $parameters = array_merge(array(
            'code'          => $request->query->get('code'),
            'grant_type'    => 'authorization_code',
            'client_id'     => $this->getOption('client_id'),
            'client_secret' => $this->getOption('client_secret'),
            'redirect_uri'  => $redirectUri,
        ), $extraParameters);

        $response = $this->doGetTokenRequest($this->getOption('access_token_url'), $parameters);
        $response = $this->getResponseContent($response);

        $this->validateResponseContent($response);

        return $response;
    }

    /**
     * {@inheritDoc}
     */
    public function refreshAccessToken($refreshToken, array $extraParameters = array())
    {
        $parameters = array_merge( array(
            'refresh_token' => $refreshToken,
            'grant_type'    => 'refresh_token',
            'client_id'     => $this->getOption('client_id'),
            'client_secret' => $this->getOption('client_secret'),
        ), $extraParameters);

        $response = $this->doGetTokenRequest($this->getOption('access_token_url'), $parameters);
        $response = $this->getResponseContent($response);

        $this->validateResponseContent($response);

        return $response;
    }

    /**
     * {@inheritDoc}
     */
    public function handles(Request $request)
    {
        return $request->query->has('code');
    }

    /**
     * {@inheritdoc}
     */
    public function isCsrfTokenValid($csrfToken)
    {
        // Mark token valid when validation is disabled
        if (!$this->getOption('csrf')) {
            return true;
        }

        try {
            return null !== $this->storage->fetch($this, urldecode($csrfToken), 'csrf_state');
        } catch (\InvalidArgumentException $e) {
            throw new AuthenticationException('Given CSRF token is not valid.');
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function doGetTokenRequest($url, array $parameters = array())
    {
        return $this->httpRequest($url, http_build_query($parameters, '', '&'));
    }

    /**
     * {@inheritDoc}
     */
    protected function doGetUserInformationRequest($url, array $parameters = array())
    {
        return $this->httpRequest($url);
    }

    /**
     * @param mixed $response the 'parsed' content based on the response headers
     *
     * @throws AuthenticationException If an OAuth error occurred or no access token is found
     */
    protected function validateResponseContent($response)
    {
        if (isset($response['error_description'])) {
            throw new AuthenticationException(sprintf('OAuth error: "%s"', $response['error_description']));
        }

        if (isset($response['error'])) {
            throw new AuthenticationException(sprintf('OAuth error: "%s"', isset($response['error']['message']) ? $response['error']['message'] : $response['error']));
        }

        if (!isset($response['access_token'])) {
            throw new AuthenticationException('Not a valid access token.');
        }
    }
}
