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

use Buzz\Message\Request as HttpRequest;
use Buzz\Message\RequestInterface as HttpRequestInterface;
use Buzz\Message\Response as HttpResponse;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * RedditResourceOwner
 *
 * @author Martin Aarhof <martin.aarhof@gmail.com>
 */
class RedditResourceOwner extends GenericOAuth2ResourceOwner
{
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
     * {@inheritDoc}
     */
    public function getUserInformation(array $accessToken, array $extraParameters = array())
    {
        $request  = new HttpRequest(HttpRequestInterface::METHOD_GET, $this->options['infos_url']);
        $response = new HttpResponse();

        $headers = array(
            'User-Agent: HWIOAuthBundle (https://github.com/hwi/HWIOAuthBundle)',
            'Authorization: Bearer ' . $accessToken['access_token'],
        );

        $request->setHeaders($headers);
        $this->httpClient->send($request, $response);
        $content = $this->getResponseContent($response);

        $response = $this->getUserResponse();
        $response->setResponse($content);
        $response->setResourceOwner($this);
        $response->setOAuthToken(new OAuthToken($accessToken));

        return $response;
    }

    /**
     * {@inheritDoc}
     */
    public function getAccessToken(Request $request, $redirectUri, array $extraParameters = array())
    {
        $parameters = array(
            'grant_type'    => 'authorization_code',
            'code'          => $request->query->get('code'),
            'redirect_uri'  => $redirectUri,
            'client_id'     =>  $this->options['client_id']
        );

        return $this->getAccessResponse($this->options['access_token_url'], $parameters);
    }

    /**
     * {@inheritDoc}
     */
    public function refreshAccessToken($refreshToken, array $extraParameters = array())
    {
        $parameters = array(
            'refresh_token' => $refreshToken,
            'grant_type'    => 'refresh_token',
            'client_id'     => $this->options['client_id'],
        );

        return $this->getAccessResponse($this->options['access_token_url'], $parameters);
    }

    /**
     * {@inheritDoc}
     */
    protected function configureOptions(OptionsResolverInterface $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'authorization_url' => 'https://ssl.reddit.com/api/v1/authorize',
            'access_token_url'  => 'https://ssl.reddit.com/api/v1/access_token',
            'infos_url'         => 'https://oauth.reddit.com/api/v1/me.json',
            'duration'          => 'permanent',
            'use_commas_in_scope' => true,
            'csrf'              => true,
            'scope'             => 'identity',
        ));
    }

    /**
     * @param $url
     * @param array $parameters
     * @return array|HttpResponse
     * @throws \Symfony\Component\Security\Core\Exception\AuthenticationException
     */
    private function getAccessResponse($url, array $parameters)
    {
        $request  = new HttpRequest(HttpRequestInterface::METHOD_POST, $url);
        $response = new HttpResponse();

        $headers = array(
            'User-Agent: HWIOAuthBundle (https://github.com/hwi/HWIOAuthBundle)',
            'Authorization: Basic ' . base64_encode(sprintf('%s:%s', $this->options['client_id'], $this->options['client_secret']))
        );

        $request->setHeaders($headers);
        $request->setContent($parameters);

        $this->httpClient->send($request, $response);
        $response = $this->getResponseContent($response);
        $this->validateResponseContent($response);

        return $response;
    }
}
