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
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\Options;

/**
 * VkontakteResourceOwner
 *
 * @author Adrov Igor <nucleartux@gmail.com>
 * @author Vladislav Vlastovskiy <me@vlastv.ru>
 * @author Alexander Latushkin <alex@skazo4neg.ru>
 */
class VkontakteResourceOwner extends GenericOAuth2ResourceOwner
{
    /**
     * {@inheritDoc}
     */
    protected $paths = array(
        'identifier' => 'response.0.uid',
        'nickname'   => 'nickname',
        'firstname'  => 'response.0.first_name',
        'lastname'   => 'response.0.last_name',
        'realname'   => array('response.0.last_name', 'response.0.first_name'),
        'profilepicture' => 'response.0.photo',
        'email'          => 'email'
    );

    /**
     * {@inheritDoc}
     */
    public function getUserInformation(array $accessToken, array $extraParameters = array())
    {
        $url = $this->normalizeUrl($this->options['infos_url'], array(
            'access_token' => $accessToken['access_token'],
            'fields'       => $this->options['fields'],
            'name_case'    => $this->options['name_case'],
        ));

        $content = $this->doGetUserInformationRequest($url)->getContent();

        $response = $this->getUserResponse();
        $response->setResponse($content);
        $response->setResourceOwner($this);
        $response->setOAuthToken(new OAuthToken($accessToken));

        $content = $response->getResponse();
        $content['email'] = isset($accessToken['email']) ? $accessToken['email'] : null;
        if (isset($content['screen_name'])) {
            $content['nickname'] = $content['screen_name'];
        } else {
            $content['nickname'] = isset($content['nickname']) ? $content['nickname'] : null;
        }

        $response->setResponse($content);

        return $response;
    }

    /**
     * {@inheritDoc}
     */
    protected function configureOptions(OptionsResolverInterface $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'authorization_url'   => 'https://oauth.vk.com/authorize',
            'access_token_url'    => 'https://oauth.vk.com/access_token',
            'infos_url'           => 'https://api.vk.com/method/users.get',

            'scope'               => 'email',

            'use_commas_in_scope' => true,

            'fields'              => 'nickname,photo_medium,screen_name,email',
            'name_case'           => null,
        ));

        $fieldsNormalizer = function (Options $options, $value) {
            if (!$value) {
                return null;
            }

            return is_array($value) ? implode(',', $value) : $value;
        };

        // Symfony <2.6 BC
        if (method_exists($resolver, 'setNormalizer')) {
            $resolver->setNormalizer('fields', $fieldsNormalizer);
        } else {
            $resolver->setNormalizers(array(
                'fields' => $fieldsNormalizer,
            ));
        }
    }
}
