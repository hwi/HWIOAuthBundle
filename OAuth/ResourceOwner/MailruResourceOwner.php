<?php

namespace HWI\Bundle\OAuthBundle\OAuth\ResourceOwner;

use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;

/**
* MailruResourceOwner
*
* @author Gregory <gridsane@gmail.com>
*/
class MailruResourceOwner extends GenericOAuth2ResourceOwner
{
    protected $defaultOptions = array(
        'client_private'    => null,
        'authorization_url' => 'https://connect.mail.ru/oauth/authorize',
        'access_token_url'  => 'https://connect.mail.ru/oauth/token',
        'infos_url'         => 'http://www.appsmail.ru/platform/api?method=users.getInfo',
        'scope'             => null,
        'csrf'              => false,
    );

    protected $paths = array(
        'identifier'     => '0.uid',
        'nickname'       => '0.nick',
        'realname'       => array('0.last_name', '0.first_name'),
        'email'          => '0.email',
        'profilepicture' => '0.pic_190',
    );

    /**
     * Override to add 'sig' parameter (http://api.mail.ru/docs/guides/restapi/#sig (Russian))
     *
     * {@inheritDoc}
     */
    public function getUserInformation(array $accessToken, array $extraParameters = array())
    {
        $params = array();
        parse_str(parse_url($this->getOption('infos_url'))['query'], $params);

        $params = array_merge($params, array(
            'app_id' => $this->getOption('client_id'),
            'session_key' => $accessToken['access_token'],
        ));

        $concatenatedParams = '';
        ksort($params);
        foreach ($params as $k => $v) {
            $concatenatedParams .= "$k=$v";
        }

        // 'x_mailru_vid' contains current user's id
        $params['sig'] = md5($accessToken['x_mailru_vid'] . $concatenatedParams . $this->getOption('client_private'));

        $url = $this->normalizeUrl($this->getOption('infos_url'), $params);

        $content = $this->doGetUserInformationRequest($url)->getContent();

        $response = $this->getUserResponse();
        $response->setResponse($content);
        $response->setResourceOwner($this);
        $response->setOAuthToken(new OAuthToken($accessToken));

        return $response;
    }
}