<?php
/**
 * Created by PhpStorm.
 * User: polidog
 * Date: 2016/06/15
 */

namespace HWI\Bundle\OAuthBundle\OAuth\ResourceOwner;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use Symfony\Component\HttpFoundation\Request;
use Buzz\Message\RequestInterface as HttpRequestInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class YahooJpResourceOwner extends GenericOAuth2ResourceOwner
{
    /**
     * {@inheritDoc}
     */
    protected $paths = array(
        'identifier' => 'user_id',
        'nickname'   => 'name',
        'realname'   => 'name',
        'firstname'   => 'given_name',
        'lastname'  => "family_name"
    );

    /**
     * {@inheritDoc}
     */
    public function getUserInformation(array $accessToken, array $extraParameters = array())
    {

        $content = $this->doGetUserInformationRequest($this->normalizeUrl($this->options['infos_url'], array('schema' => 'openid')),array(
            'access_token' => $accessToken['access_token']
        ));

        $response = $this->getUserResponse();
        $response->setResponse($content->getContent());

        $response->setResourceOwner($this);
        $response->setOAuthToken(new OAuthToken($accessToken));

        return $response;
    }

    public function getAccessToken(Request $request, $redirectUri, array $extraParameters = array())
    {
        $parameters = array_merge(array(
            'code'          => $request->query->get('code'),
            'grant_type'    => 'authorization_code',
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
        ), $extraParameters);

        $response = $this->doGetTokenRequest($this->options['access_token_url'], $parameters);
        $response = $this->getResponseContent($response);

        $this->validateResponseContent($response);

        return $response;
    }

    /**
     * {@inheritDoc}
     */
    protected function doGetTokenRequest($url, array $parameters = array())
    {
        $headers = array(
            'Authorization: Basic ' . base64_encode($this->options['client_id'] . ':' . $this->options['client_secret']),
            'Content-Type: application/x-www-form-urlencoded',
        );
        return $this->httpRequest($this->options['access_token_url'], http_build_query($parameters, '', '&'), $headers, HttpRequestInterface::METHOD_POST);
    }

    /**
     * {@inheritDoc}
     */
    protected function doGetUserInformationRequest($url, array $parameters = array())
    {
        $headers = array(
            'Authorization: Bearer ' . $parameters['access_token'],
        );
        return $this->httpRequest($url, http_build_query($parameters, '', '&'), $headers);
    }

    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults(array(
            'authorization_url' => 'https://auth.login.yahoo.co.jp/yconnect/v1/authorization',
            'access_token_url'  => 'https://auth.login.yahoo.co.jp/yconnect/v1/token',
            'infos_url'         => 'https://userinfo.yahooapis.jp/yconnect/v1/attribute',

            'scope'             => 'openid,profile',

            'use_bearer_authorization' => false,
            'use_commas_in_scope' => true
        ));
    }
}