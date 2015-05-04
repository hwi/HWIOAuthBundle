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

use Buzz\Message\MessageInterface as HttpMessageInterface;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class WechatResourceOwner extends GenericOAuth2ResourceOwner
{
    /**
     * {@inheritDoc}
     */
    protected $paths = array(
        'identifier'     => 'openid',
        'nickname'       => 'nickname',
        'realname'       => 'nickname',
        'profilepicture' => 'headimgurl',
    );

    public function getAccessToken(Request $request, $redirectUri, array $extraParameters = array())
    {
        $parameters = array_merge(array(
            'appid'      => $this->options['client_id'],
            'secret'     => $this->options['client_secret'],
            'code'       => $request->query->get('code'),
            'grant_type' => 'authorization_code',
        ), $extraParameters);

        $response = $this->doGetTokenRequest($this->options['access_token_url'], $parameters);
        $content = $this->getResponseContent($response);

        $this->validateResponseContent($content);

        return $response;
    }

    /**
     * {@inheritDoc}
     */
    public function getAuthorizationUrl($redirectUri, array $extraParameters = array())
    {
        $parameters = array_merge(array(
            'appid'         => $this->options['client_id'],
            'redirect_uri'  => $redirectUri,
            'response_type' => 'code',
            'scope'         => $this->options['scope'],
            'state'         => $this->state ? urlencode($this->state) : null,
        ), $extraParameters);

        ksort($parameters); // i don't know why, but the order of the parameters REALLY matters

        return $this->normalizeUrl($this->options['authorization_url'], $parameters);
    }

    /**
     * {@inheritDoc}
     */
    public function getUserInformation(array $accessToken = null, array $extraParameters = array())
    {
        $accessToken = $this->getResponseContent($accessToken['access_token']);
        if ('snsapi_userinfo' === $this->options['scope']) {
            $openid = $accessToken['openid'];

            $url = $this->normalizeUrl($this->options['infos_url'], array(
                'access_token' => $accessToken['access_token'],
                'openid'       => $openid,
            ));

            $response = $this->doGetUserInformationRequest($url);
            $content = $this->getResponseContent($response);
        } else {
            $content = array(
                'openid' => $accessToken['openid'],
            );
        }

        $this->validateResponseContent($content);

        $response = $this->getUserResponse();
        $response->setResponse($content);
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

        $resolver->setDefaults(array(
            'authorization_url' => 'https://open.weixin.qq.com/connect/oauth2/authorize',
            'access_token_url'  => 'https://api.weixin.qq.com/sns/oauth2/access_token',
            'infos_url'         => 'https://api.weixin.qq.com/sns/userinfo',
        ));
    }

    /**
     * {@inheritDoc}
     */
    protected function validateResponseContent($response)
    {
        if (isset($response['errmsg'])) {
            throw new AuthenticationException(sprintf('OAuth error: "%s"', $response['errmsg']));
        }
    }
}
