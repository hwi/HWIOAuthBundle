<?php

namespace Knp\OAuthBundle\Security\Http\OAuth;

use Knp\OAuthBundle\Security\Http\OAuth\OAuthProviderInterface;

use Buzz\Client\ClientInterface as HttpClientInterface,
    Buzz\Message\Request as HttpRequest,
    Buzz\Message\Response as HttpResponse;


class OAuthProvider implements OAuthProviderInterface
{
    private $options = array();

    private $httpClient;

    public function __construct(HttpClientInterface $httpClient, array $options)
    {
        $this->httpClient = $httpClient;
        $this->options    = $options;
    }

    public function getOption($name)
    {
        if (!isset($this->options[$name])) {
            throw new \InvalidArgumentException(sprintf('Unknown option "%s"', $name));
        }

        return $this->options[$name];
    }

    protected function httpRequest($url, $method = HttpRequest::METHOD_GET)
    {
        $request  = new HttpRequest($method, $url);
        $response = new HttpResponse();

        $this->httpClient->send($request, $response);

        return $response->getContent();
    }

    public function getUsername($accessToken)
    {
        $url = $this->getOption('infos_url').'?'.http_build_query(array(
            'access_token' => $accessToken
        ));

        $userInfos    = json_decode($this->httpRequest($url), true);
        $usernamePath = explode('.', $this->getOption('username_path'));

        $username     = $userInfos;

        foreach ($usernamePath as $path) {
            $username = $username[$path];
        }

        return $username;
    }

    public function getAuthorizationUrl(array $extraParameters = array())
    {
        $parameters = array_merge($extraParameters, array(
            'response_type' => 'code',
            'client_id'     => $this->getOption('client_id'),
            'scope'         => $this->getOption('scope'),
        ));

        return $this->getOption('authorization_url').'?'.http_build_query($parameters);
    }

    public function getAccessTokenUrl($code, array $extraParameters = array())
    {
        $parameters = array_merge($extraParameters, array(
            'code'          => $code,
            'grant_type'    => 'authorization_code',
            'client_id'     => $this->getOption('client_id'),
            'client_secret' => $this->getOption('secret'),
        ));

        return $this->getOption('access_token_url').'?'.http_build_query($parameters);
    }
}