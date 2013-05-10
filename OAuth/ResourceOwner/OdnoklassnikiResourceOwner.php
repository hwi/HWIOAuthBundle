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
    /**
     * {@inheritDoc}
     */
    protected $options = array(
        'authorization_url'   => 'http://www.odnoklassniki.ru/oauth/authorize',
        'access_token_url'    => 'http://api.odnoklassniki.ru/oauth/token.do',
        'infos_url'           => 'http://api.odnoklassniki.ru/fb.do?method=users.getCurrentUser',
        'scope'               => null,
        'user_response_class' => '\HWI\Bundle\OAuthBundle\OAuth\Response\PathUserResponse',

        'application_key'     => null,
    );

    /**
     * {@inheritDoc}
     */
    protected $paths = array(
        'identifier' => 'uid',
        'nickname'   => 'username',
        'realname'   => 'name',
    );

    /**
     * {@inheritDoc}
     */
    public function getUserInformation($accessToken)
    {
        $parameters = array(
            'access_token'    => $accessToken,
            'application_key' => $this->getOption('application_key'),
            'sig'             => md5(sprintf('application_key=%smethod=users.getCurrentUser%s', $this->getOption('application_key'), md5($accessToken.$this->getOption('client_secret')))),
        );
        $url = $this->normalizeUrl($this->getOption('infos_url'), $parameters);

        $content = $this->doGetUserInformationRequest($url)->getContent();

        $response = $this->getUserResponse();
        $response->setResponse($content);
        $response->setResourceOwner($this);
        $response->setAccessToken($accessToken);

        return $response;
    }
}
