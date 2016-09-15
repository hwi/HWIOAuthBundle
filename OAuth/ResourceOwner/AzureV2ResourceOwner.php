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

use Buzz\Message\RequestInterface as HttpRequestInterface;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\HttpFoundation\Request;

use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;

/**
 * AzureV2ResourceOwner
 *
 * @author Remy Gazelot <r.gazelot@gmail.com>
 */
class AzureV2ResourceOwner extends GenericOAuth2ResourceOwner
{
    /**
     * {@inheritDoc}
     */
    protected $paths = array(
        'identifier'    => 'id',
        'email'         => 'mail',
        'nickname'      => 'mail',
        'realname'      => 'displayName',
        'firstName'     => 'givenName',
        'lastName'      => 'surname'
    );

    /**
     * {@inheritDoc}
     */
    public function configure()
    {
        $this->options['access_token_url'] = sprintf($this->options['access_token_url'], $this->options['application'], $this->options['api_version']);
        $this->options['authorization_url'] = sprintf($this->options['authorization_url'], $this->options['application'], $this->options['api_version']);
    }

    /**
     * {@inheritDoc}
     */
    public function getAuthorizationUrl($redirectUri, array $extraParameters = array())
    {
        return parent::getAuthorizationUrl($redirectUri, $extraParameters);
    }

    public function getAccessToken(Request $request, $redirectUri, array $extraParameters = array())
    {
        $parameters = array_merge(array(
            'code'          => $request->query->get('code'),
            'grant_type'    => 'authorization_code',
            'client_id'     => $this->options['client_id'],
            'client_secret' => $this->options['client_secret'],
            'redirect_uri'  => $redirectUri,
        ), $extraParameters);

        $response = $this->httpRequest($this->options['access_token_url'], http_build_query($parameters, '', '&'), [], HttpRequestInterface::METHOD_POST);
        $response = $this->getResponseContent($response);

        $this->validateResponseContent($response);

        return $response;
    }

    /**
     * {@inheritDoc}
     */
    public function refreshAccessToken($refreshToken, array $extraParameters = array())
    {
        return parent::refreshAccessToken($refreshToken, $extraParameters);
    }

    /**
     * {@inheritDoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setRequired(array('scope'));

        $resolver->setDefaults(array(
            'infos_url' => 'https://graph.microsoft.com/v1.0/me',
            'authorization_url' => 'https://login.microsoftonline.com/%s/oauth2/%s/authorize',
            'access_token_url' => 'https://login.microsoftonline.com/%s/oauth2/%s/token',
            'application' => 'common',
            'api_version' => 'v2.0',
            'use_bearer_authorization' => true,
            'csrf' => true
        ));
    }
}
