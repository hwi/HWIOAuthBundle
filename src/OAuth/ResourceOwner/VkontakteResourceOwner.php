<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\OAuth\ResourceOwner;

use HWI\Bundle\OAuthBundle\OAuth\Exception\HttpTransportException;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use Symfony\Component\HttpClient\Exception\JsonException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * @author Adrov Igor <nucleartux@gmail.com>
 * @author Vladislav Vlastovskiy <me@vlastv.ru>
 * @author Alexander Latushkin <alex@skazo4neg.ru>
 */
final class VkontakteResourceOwner extends GenericOAuth2ResourceOwner
{
    public const TYPE = 'vkontakte';

    /**
     * {@inheritdoc}
     */
    protected array $paths = [
        'identifier' => 'response.0.id',
        'nickname' => 'response.0.nickname',
        'firstname' => 'response.0.first_name',
        'lastname' => 'response.0.last_name',
        'realname' => ['response.0.last_name', 'response.0.first_name'],
        'profilepicture' => 'response.0.photo_medium',
        'email' => 'email',
    ];

    /**
     * {@inheritdoc}
     */
    public function getUserInformation(array $accessToken, array $extraParameters = [])
    {
        $url = $this->normalizeUrl($this->options['infos_url'], [
            'access_token' => $accessToken['access_token'],
            'fields' => $this->options['fields'],
            'name_case' => $this->options['name_case'],
            'v' => $this->options['api_version'],
        ]);

        try {
            $response = $this->getUserResponse();
            $response->setResourceOwner($this);
            $response->setOAuthToken(new OAuthToken($accessToken));

            $content = $this->doGetUserInformationRequest($url)->toArray(false);
            $content['email'] = $accessToken['email'] ?? null;

            if (isset($content['response'][0]['screen_name'])) {
                $content['response'][0]['nickname'] = $content['response'][0]['screen_name'];
            }

            $response->setData($content);

            return $response;
        } catch (TransportExceptionInterface|JsonException $e) {
            throw new HttpTransportException('Error while sending HTTP request', $this->getName(), $e->getCode(), $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'authorization_url' => 'https://oauth.vk.com/authorize',
            'access_token_url' => 'https://oauth.vk.com/access_token',
            'infos_url' => 'https://api.vk.com/method/users.get',
            'use_authorization_to_get_token' => false,

            // Based on: https://vk.com/dev/constant_version_updates
            'api_version' => '5.131',

            'scope' => 'email',

            'use_commas_in_scope' => true,

            'fields' => 'nickname,photo_medium,screen_name,email',
            'name_case' => null,
        ]);

        $fieldsNormalizer = function (Options $options, $value) {
            if (!$value) {
                return null;
            }

            return \is_array($value) ? implode(',', $value) : $value;
        };

        $resolver->setNormalizer('fields', $fieldsNormalizer);
    }
}
