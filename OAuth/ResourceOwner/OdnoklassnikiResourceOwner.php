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

/**
 * OdnoklassnikiResourceOwner
 *
 * @author Sergey Polischook <spolischook@gmail.com>
 */
class OdnoklassnikiResourceOwner extends GenericOAuth2ResourceOwner
{
    private $odnoklassnikiAppPublic;

    /**
     * {@inheritDoc}
     */
    protected $options = array(
        'authorization_url'   => 'http://www.odnoklassniki.ru/oauth/authorize',
        'access_token_url'    => 'http://api.odnoklassniki.ru/oauth/token.do',
        'infos_url'           => 'http://api.odnoklassniki.ru/fb.do?method=users.getCurrentUser',
        'scope'               => '',
        'user_response_class' => '\HWI\Bundle\OAuthBundle\OAuth\Response\PathUserResponse',
    );

    /**
     * {@inheritDoc}
     */
    protected $paths = array(
        'identifier' => 'uid',
        'nickname'   => 'username',
        'realname'   => 'name',
    );

    public function getUserInformation($accessToken)
    {
        $url = $this->getOption('infos_url');
        $sig = md5(
            'application_key=' . $this->odnoklassnikiAppPublic .
                'method=users.getCurrentUser' .
                md5($accessToken . $this->getOption('client_secret'))
        );
        $arrayParameters = array(
            'access_token' => $accessToken,
            'application_key' => $this->odnoklassnikiAppPublic,
            'sig' => $sig,
        );
        $url .= (false !== strpos($url, '?') ? '&' : '?').http_build_query($arrayParameters);

        $content = $this->doGetUserInformationRequest($url)->getContent();

        $response = $this->getUserResponse();
        $response->setResponse($content);
        $response->setResourceOwner($this);
        $response->setAccessToken($accessToken);

        return $response;
    }

    public function setParameters($parameters)
    {
        $this->odnoklassnikiAppPublic = $parameters['odnoklassniki_app_public'];
    }
}
