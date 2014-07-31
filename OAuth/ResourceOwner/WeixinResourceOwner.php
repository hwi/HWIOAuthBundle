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

use Symfony\Component\HttpFoundation\Request;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * GoogleResourceOwner
 *
 * @author Algo <progralgo@gmail.com>
 */
class WeixinResourceOwner extends GenericOAuth2ResourceOwner
{
    /**
     * {@inheritDoc}
     */
    protected function configureOptions(OptionsResolverInterface $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'access_token_url'  => 'https://api.weixin.qq.com/sns/oauth2/access_token',
            'authorization_url' => 'https://open.weixin.qq.com/connect/oauth2/authorize',
            'infos_url'         => 'https://api.weixin.qq.com/sns/userinfo',
        ));
    }

    /**
     * {@inheritDoc}
     */
    protected $paths = array(
        'identifier'     => 'openid',
        'nickname'       => 'nickname',
        'realname'       => 'nickname',
        'profilepicture' => 'headimgurl',
    );

    /**
     * {@inheritDoc}
     */
    public function getUserInformation(array $accessToken, array $extraParameters = array())
    {
        $openid = $accessToken['openid'];

        $url = $this->normalizeUrl($this->options['infos_url'], array(
            'access_token' => $accessToken['access_token'],
            'openid'       => $openid,
            'lang'         => 'zh_CN'
        ));

        $content = $this->doGetUserInformationRequest($url);
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

        $this->state = 12;
        $parameters = array_merge(array(
            'appid'         => $this->options['client_id'],
            'redirect_uri'  => $redirectUri,
            'response_type' => 'code',
            'scope'         => $this->options['scope'],
            'state'         => $this->state ? urlencode($this->state) : null,
        ), $extraParameters);

        $authorizationUrl = $this->normalizeUrl($this->options['authorization_url'], $parameters)."#wechat_redirect";
        return $authorizationUrl;
    }

    /**
     * {@inheritDoc}
     */
    public function getAccessToken(Request $request, $redirectUri, array $extraParameters = array())
    {
        $parameters = array_merge(array(
            'appid'      => $this->options['client_id'],
            'secret'     => $this->options['client_secret'],
            'code'       => $request->query->get('code'),
            'grant_type' => 'authorization_code',
        ), $extraParameters);

        $response = $this->doGetTokenRequest($this->options['access_token_url'], $parameters);
        $response = $this->getResponseContent($response);

        $this->validateResponseContent($response);

        return $response;
    }
}
