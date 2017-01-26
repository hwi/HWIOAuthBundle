<?php

namespace HWI\Bundle\OAuthBundle\OAuth\ResourceOwner;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class FigoResourceOwner.
 */
class FigoResourceOwner extends GenericOAuth2ResourceOwner
{
    /**
     * {@inheritdoc}
     */
    protected $paths = [
        'identifier' => 'user_id',
        'nickname' => 'name',
        'realname' => 'name',
        'email' => 'email',
    ];

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'authorization_url' => 'https://api.figo.me/auth/code',
            'access_token_url' => 'https://api.figo.me/auth/token',
            'infos_url' => 'https://api.figo.me/rest/user',
            'use_bearer_authorization' => true,
            'csrf' => true,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessToken(Request $request, $redirectUri, array $extraParameters = [])
    {
        $parameters = array_merge([
            'code' => $request->query->get('code'),
            'grant_type' => 'authorization_code',
            'redirect_uri' => $redirectUri,
        ], $extraParameters);

        $basicAuthHash = base64_encode(sprintf('%s:%s', $this->options['client_id'], $this->options['client_secret']));
        $headers = ['Authorization: Basic '.$basicAuthHash];

        $response = $this->doFigoGetTokenRequest($this->options['access_token_url'], $parameters, $headers);
        $response = $this->getResponseContent($response);

        $this->validateResponseContent($response);

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function refreshAccessToken($refreshToken, array $extraParameters = array())
    {
        $parameters = array_merge(array(
            'refresh_token' => $refreshToken,
            'grant_type' => 'refresh_token',
        ), $extraParameters);

        $basicAuthHash = base64_encode(sprintf('%s:%s', $this->options['client_id'], $this->options['client_secret']));
        $headers = ['Authorization: Basic '.$basicAuthHash];

        $response = $this->doFigoGetTokenRequest($this->options['access_token_url'], $parameters, $headers);
        $response = $this->getResponseContent($response);

        $this->validateResponseContent($response);

        return $response;
    }

    /**
     * @param string $name
     * @param string $email
     * @param string $password
     *
     * @return array
     */
    public function createFigoUser($name, $email, $password)
    {
        $parameters = array(
            'name' => $name,
            'email' => $email,
            'password' => $password,
        );

        $basicAuthHash = base64_encode(sprintf('%s:%s', $this->options['client_id'], $this->options['client_secret']));
        $headers = ['Authorization: Basic '.$basicAuthHash];

        $response = $this->doFigoGetTokenRequest($this->options['create_user_url'], $parameters, $headers);
        $response = $this->getResponseContent($response);

        $this->validateResponseContent($response);

        return $response;
    }

    /**
     * @param string $password
     * @param string $userName
     *
     * @return array
     */
    public function loginFigoUser($password, $userName)
    {
        $parameters = array(
            'password' => $password,
            'username' => $userName,
            'grant_type' => 'password',
        );

        $basicAuthHash = base64_encode(sprintf('%s:%s', $this->options['client_id'], $this->options['client_secret']));
        $headers = ['Authorization: Basic '.$basicAuthHash];

        $response = $this->doFigoGetTokenRequest($this->options['create_user_url'], $parameters, $headers);
        $response = $this->getResponseContent($response);

        $this->validateResponseContent($response);

        return $response;
    }

    /**
     * @param string $url
     * @param array  $parameters
     * @param array  $headers
     *
     * @return \Buzz\Message\Response
     */
    protected function doFigoGetTokenRequest($url, array $parameters = [], array $headers)
    {
        return $this->httpRequest($url, http_build_query($parameters, '', '&'), $headers);
    }
}
