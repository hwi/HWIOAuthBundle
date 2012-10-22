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

use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * VkontakteResourceOwner
 *
 * @author Adrov Igor <nucleartux@gmail.com>
 */
class VkontakteResourceOwner extends GenericOAuth2ResourceOwner
{
    /**
     * {@inheritDoc}
     */
    protected $options = array(
        'authorization_url'   => 'https://api.vk.com/oauth/authorize',
        'access_token_url'    => 'https://oauth.vk.com/access_token',
        'infos_url'           => 'https://api.vk.com/method/getUserInfoEx',
        'extend_info_url'     => 'https://api.vk.com/method/users.get',
        'scope'               => '',
        'user_response_class' => '\HWI\Bundle\OAuthBundle\OAuth\Response\PathUserResponse',
    );

    /**
     * {@inheritDoc}
     */
    protected $paths = array(
        'identifier' => 'response.uid',
        'nickname'   => 'response.nickname',
        'realname'   => 'response.screen_name',
        'profilepicture' => 'response.photo_big',
    );

    /**
     * Vkontakte unfortunately breaks the spec by using commas instead of spaces
     * to separate scopes
     */
    public function configure()
    {
        $this->options['scope'] = str_replace(',', ' ', $this->options['scope']);
    }

    /**
     * {@inheritDoc}
     */
    public function getUserInformation($accessToken)
    {
        $url = $this->getOption('infos_url');
        $url .= (false !== strpos($url, '?') ? '&' : '?').http_build_query(array(
            'access_token' => $accessToken
        ));

        $content = $this->doGetUserInformationRequest($url)->getContent();

        $response = $this->getUserResponse();
        $response->setResponse($content);
        $response->setResourceOwner($this);
        $response->setAccessToken($accessToken);

        $response = $response->getResponse();

        //Create request for more information

        $url = $this->getOption('extend_info_url');
        $url .= (false !== strpos($url, '?') ? '&' : '?').http_build_query(array(
            'access_token' => $accessToken
        ));
        $url .= '&uids=' . $response['response']['user_id'] . '&fields=first_name,last_name,nickname,screen_name,sex,photo_big';

        $response = $this->getUserResponse();

        $content = $this->doGetUserInformationRequest($url)->getContent();
        $content = json_decode($content, true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new AuthenticationException(sprintf('Not a valid JSON response.'));
        }

        $responseParameter = array(
            'response' => $content['response'][0],
        );

        $response->setResponse(json_encode($responseParameter));
        $response->setResourceOwner($this);
        $response->setAccessToken($accessToken);

        return $response;
    }
}
