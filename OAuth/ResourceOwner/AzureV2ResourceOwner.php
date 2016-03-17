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

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
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
        'identifier'     => 'sub',
        'nickname'       => 'preferred_username',
        'realname'       => 'name',
        'email'          => 'email',
        'profilepicture' => null,
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
    public function getUserInformation(array $accessToken, array $extraParameters = array())
    {
        // from http://stackoverflow.com/a/28748285/624544
        list(, $jwt, ) = explode('.', $accessToken['id_token'], 3);

        // if the token was urlencoded, do some fixes to ensure that it is valid base64 encoded
        $jwt = str_replace('-', '+', $jwt);
        $jwt = str_replace('_', '/', $jwt);

        // complete token if needed
        switch (strlen($jwt) % 4) {
            case 0:
                break;

            case 2:
                $jwt .= '=';

            case 3:
                $jwt .= '=';
                break;

            default:
                throw new \InvalidArgumentException('Invalid base64 format sent back');
        }

        $response = $this->getUserResponse();
        $response->setResponse(base64_decode($jwt));

        $response->setResourceOwner($this);
        $response->setOAuthToken(new OAuthToken($accessToken));

        return $response;
    }

    /**
     * {@inheritDoc}
     */
    protected function configureOptions(OptionsResolverInterface $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setRequired(array('scope'));

        $resolver->setDefaults(array(
            'infos_url' => '',
            'authorization_url' => 'https://login.microsoftonline.com/%s/oauth2/%s/authorize',
            'access_token_url' => 'https://login.microsoftonline.com/%s/oauth2/%s/token',
            'application' => 'common',
            'api_version' => 'v2.0',
            'use_bearer_authorization' => true,
            'csrf' => true
        ));
    }
}
