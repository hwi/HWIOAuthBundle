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
use Symfony\Component\HttpFoundation\Request;

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
        'infos_url'           => 'https://api.vk.com/method/users.get',
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
    public function getUserInformation($accessToken, $user_id = null)
    {
        if(is_array($accessToken)) {
            $user_id = $accessToken['user_id'];
            $accessToken = $accessToken['access_token'];
        }
        $url = $this->getOption('infos_url');
        $url .= (false !== strpos($url, '?') ? '&' : '?').http_build_query(array(
            'access_token' => $accessToken,
        ));
        $url .= '&uids=' . $user_id . '&fields=first_name,last_name,nickname,screen_name,sex,photo_big';

        $userInfo = $this->doGetUserInformationRequest($url)->getContent();

        $content = json_decode($userInfo, true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new AuthenticationException(sprintf('Not a valid JSON response.'));
        }

        $response = $this->getUserResponse();
        $response->setResponse(json_encode(array('response' => $content['response'][0])));
        $response->setResourceOwner($this);
        $response->setAccessToken($accessToken);

        return $response;
    }

    /**
     * {@inheritDoc}
     */
    public function getAccessToken(Request $request, $redirectUri, array $extraParameters = array())
    {
        $parameters = array_merge($extraParameters, array(
            'code'          => $request->query->get('code'),
            'grant_type'    => 'authorization_code',
            'client_id'     => $this->getOption('client_id'),
            'client_secret' => $this->getOption('client_secret'),
            'redirect_uri'  => $redirectUri,
        ));

        $response = $this->doGetAccessTokenRequest($this->getOption('access_token_url'), $parameters);
        $response = $this->getResponseContent($response);

        if (isset($response['error'])) {
            throw new AuthenticationException(sprintf('OAuth error: "%s"', $response['error']));
        }

        if (!isset($response['access_token'])) {
            throw new AuthenticationException('Not a valid access token.');
        }

        return $response;
    }
}
