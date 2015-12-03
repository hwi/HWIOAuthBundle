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
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
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
     * {@inheritDoc}
     */
    public function getUserInformation(array $accessToken, array $extraParameters = array())
    {
        if ($this->options['use_bearer_authorization']) {
            $content = $this->httpRequest($this->normalizeUrl($this->options['infos_url'], $extraParameters), null, array('Authorization: Bearer '.$accessToken['access_token']));
        } else {
            $content = $this->doGetUserInformationRequest($this->normalizeUrl($this->options['infos_url'], array_merge(array($this->options['attr_name'] => $accessToken['access_token']), $extraParameters)));
        }

        $response = $this->getUserResponse();
        $response->setResponse($content->getContent());

        $response->setResourceOwner($this);
        $response->setOAuthToken(new OAuthToken($accessToken));

        return $response;
    }

    /**
     * {@inheritDoc}
     */
    public function getAuthorizationUrl($redirectUri, array $extraParameters = array())
    {
        if ($this->options['csrf']) {
            if (null === $this->state) {
                $this->state = $this->generateNonce();
            }

            $this->storage->save($this, $this->state, 'csrf_state');
        }

        $parameters = array_merge(array(
            'response_type' => 'code',
            'client_id'     => $this->options['client_id'],
            'scope'         => $this->options['scope'],
            'state'         => $this->state ? urlencode($this->state) : null,
            'redirect_uri'  => $redirectUri,
        ), $extraParameters);

        return $this->normalizeUrl($this->options['authorization_url'], $parameters);
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
            'client_id'     => $this->options['client_id'],
            'client_secret' => $this->options['client_secret'],
            'redirect_uri'  => $redirectUri,
        ), $extraParameters);

        $response = $this->doGetTokenRequest($this->options['access_token_url'], $parameters);
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
            'client_id'     => $this->options['client_id'],
            'client_secret' => $this->options['client_secret'],
        ), $extraParameters);

        $response = $this->doGetTokenRequest($this->options['access_token_url'], $parameters);
        $response = $this->getResponseContent($response);

        $this->validateResponseContent($response);

        return $response;
    }

    /**
     * {@inheritDoc}
     */
    public function revokeToken($token)
    {
        if (!isset($this->options['revoke_token_url'])) {
            throw new AuthenticationException('OAuth error: "Method unsupported."');
        }

        $parameters = array(
            'client_id'     => $this->options['client_id'],
            'client_secret' => $this->options['client_secret'],
        );

        $response = $this->httpRequest($this->normalizeUrl($this->options['revoke_token_url'], array('token' => $token)), $parameters, array(), 'DELETE');

        return 200 === $response->getStatusCode();
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
        if (!$this->options['csrf']) {
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
        return $this->httpRequest($url, http_build_query($parameters, '', '&'));
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

    /**
     * {@inheritDoc}
     */
    protected function configureOptions(OptionsResolverInterface $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'attr_name'                => 'access_token',
            'use_commas_in_scope'      => false,
            'use_bearer_authorization' => true,
        ));

        // Symfony <2.6 BC
        if (method_exists($resolver, 'setDefined')) {
            $resolver->setDefined('revoke_token_url');
        } else {
            $resolver->setOptional(array(
                'revoke_token_url',
            ));
        }

        // Unfortunately some resource owners break the spec by using commas instead
        // of spaces to separate scopes (Disqus, Facebook, Github, Vkontante)
        $scopeNormalizer = function (Options $options, $value) {
            if (!$value) {
                return null;
            }

            if (!$options['use_commas_in_scope']) {
                return $value;
            }

            return str_replace(',', ' ', $value);
        };

        // Symfony <2.6 BC
        if (method_exists($resolver, 'setNormalizer')) {
            $resolver->setNormalizer('scope', $scopeNormalizer);
        } else {
            $resolver->setNormalizers(array(
                'scope' => $scopeNormalizer,
            ));
        }
    }
}
