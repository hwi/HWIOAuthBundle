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
use HWI\Bundle\OAuthBundle\Security\OAuthUtils;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Dmitry Matora <dmitry.matora@gmail.com>
 */
class ZenmoneyResourceOwner extends GenericOAuth1ResourceOwner
{
    /**
     * {@inheritdoc}
     */
    protected $paths = array(
        'identifier' => 'profile.login',
        'nickname' => 'profile.login',
        'realname' => 'profile.email',
    );

    /**
     * Override to replace {guid} in the infos_url with the authenticating user's yahoo id.
     *
     * {@inheritdoc}
     */
    public function getUserInformation(array $accessToken, array $extraParameters = array())
    {
        return parent::getUserInformation($accessToken, $extraParameters);
    }

    /**
     * Override to set the Accept header as otherwise Yahoo defaults to XML.
     *
     * {@inheritdoc}
     */
    public function doApiRequest($path, array $accessToken, array $extraParameters = array())
    {
        $parameters = array_merge([
            'oauth_consumer_key' => $this->options['client_id'],
            'oauth_timestamp' => time(),
            'oauth_nonce' => $this->generateNonce(),
            'oauth_version' => '1.0',
            'oauth_signature_method' => $this->options['signature_method'],
            'oauth_token' => $accessToken['oauth_token'],
        ], $extraParameters);

        $url = 'http://api.zenmoney.ru'.$path;
        $parameters['oauth_signature'] = OAuthUtils::signRequest(
            'GET',
            $url,
            $parameters,
            $this->options['client_secret'],
            $accessToken['oauth_token_secret'],
            $this->options['signature_method']
        );

        $content = $this->doGetUserInformationRequest($url, $parameters);
        if (200 != $content->getStatusCode()) {
            throw new \Exception($content->getStatusCode().' - '.$content->getReasonPhrase());
        }

        $response = $this->getUserResponse();
        $response->setData($content instanceof ResponseInterface ? (string) $content->getBody() : $content);
        $response->setResourceOwner($this);
        $response->setOAuthToken(new OAuthToken($accessToken));

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'authorization_url' => 'http://api.zenmoney.ru/access/',
            'request_token_url' => 'http://api.zenmoney.ru/oauth/request_token',
            'access_token_url' => 'http://api.zenmoney.ru/oauth/access_token',
            'infos_url' => 'http://api.zenmoney.ru/v1/owner/',
        ));
    }
}
