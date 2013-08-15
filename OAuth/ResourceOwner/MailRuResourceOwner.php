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

/**
 * MailRuResourceOwner
 *
 * @author Gaponov Igor <jiminy96@gmail.com>
 */
class MailRuResourceOwner extends GenericOAuth2ResourceOwner
{
    /**
     * {@inheritDoc}
     */
    protected $options = array(
        'authorization_url'   => 'https://connect.mail.ru/oauth/authorize',
        'access_token_url'    => 'https://connect.mail.ru/oauth/token',
        'infos_url'           => 'http://www.appsmail.ru/platform/api',
    );

    /**
     * {@inheritDoc}
     */
    protected $paths = array(
        'identifier' => 'uid',
        'nickname'   => 'nick',
        'realname'   => 'nick',
        'email'      => 'email',
    );

    /**
     * {@inheritDoc}
     */
    public function getUserInformation(array $accessToken, array $extraParameters = array())
    {
        $params = array(
            'app_id'    => $this->getOption('client_id'),
            'method' => 'users.getInfo',
            'secure' => '1',
            'session_key' => $accessToken['access_token'],
        );

        $sig = vprintf('app_id=%smethod=%ssecure=%ssession_key=%s', $params);

        $params['sig'] = md5($sig . $this->getOption('client_secret'));

        $url = $this->normalizeUrl($this->getOption('infos_url'), $params);

        $content = $this->doGetUserInformationRequest($url)->getContent();
        $content = json_decode($content);
        if (isset($content[0])) {
            $content = (array) $content[0];
        }

        $response = $this->getUserResponse();
        $response->setResponse($content);
        $response->setResourceOwner($this);
        $response->setOAuthToken(new OAuthToken($accessToken));

        return $response;
    }
}