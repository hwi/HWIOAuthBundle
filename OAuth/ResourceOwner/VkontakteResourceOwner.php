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
 * VkontakteResourceOwner
 *
 * @author Adrov Igor <nucleartux@gmail.com>
 * @author Vladislav Vlastovskiy <me@vlastv.ru>
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

        'user_response_class' => '\HWI\Bundle\OAuthBundle\OAuth\Response\VkontakteUserResponse',

        'fields' => 'nickname,photo_50',
        'name_case' => null,
    );

    /**
     * {@inheritDoc}
     */
    protected $paths = array(
        'identifier' => 'response.0.uid',
        'nickname'   => 'response.0.nickname',
        'last_name'   => 'response.0.last_name',
        'first_name' => 'response.0.first_name',
    );

    /**
     * Vkontakte unfortunately breaks the spec by using commas instead of spaces
     * to separate scopes
     */
    public function configure()
    {
        if (isset($this->options['scope'])) {
            $this->options['scope'] = str_replace(',', ' ', $this->options['scope']);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getUserInformation(array $accessToken, array $extraParameters = array())
    {
        $parameters = array(
            'access_token'    => $accessToken['access_token'],
            'fields' => is_array($fields = $this->getOption('fields')) ? implode(',', $fields) : $fields,
            'name_case' => $this->getOption('name_case'),
        );
        $url = $this->normalizeUrl($this->getOption('infos_url'), $parameters);

        $content = $this->doGetUserInformationRequest($url)->getContent();

        $response = $this->getUserResponse();
        $response->setResponse($content);
        $response->setResourceOwner($this);
        $response->setOAuthToken(new OAuthToken($accessToken));

        return $response;
    }
}
