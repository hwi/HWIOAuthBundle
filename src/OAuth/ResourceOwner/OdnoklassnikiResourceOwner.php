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
 * @author Sergey Polischook <spolischook@gmail.com>
 */
final class OdnoklassnikiResourceOwner extends GenericOAuth2ResourceOwner
{
    public const TYPE = 'odnoklassniki';

    /**
     * {@inheritdoc}
     */
    protected array $paths = [
        'identifier' => 'uid',
        'nickname' => 'username',
        'realname' => 'name',
        'email' => 'email',
        'firstname' => 'first_name',
        'lastname' => 'last_name',
    ];

    /**
     * {@inheritdoc}
     */
    public function getUserInformation(array $accessToken, array $extraParameters = [])
    {
        $parameters = [
            'access_token' => $accessToken['access_token'],
            'application_key' => $this->options['application_key'],
        ];

        if ($this->options['fields']) {
            $parameters['fields'] = $this->options['fields'];
            $parameters['sig'] = md5(sprintf(
                'application_key=%sfields=%smethod=users.getCurrentUser%s',
                $this->options['application_key'],
                $this->options['fields'],
                md5($accessToken['access_token'].$this->options['client_secret'])
            ));
        } else {
            $parameters['sig'] = md5(sprintf(
                'application_key=%smethod=users.getCurrentUser%s',
                $this->options['application_key'],
                md5($accessToken['access_token'].$this->options['client_secret'])
            ));
        }

        $url = $this->normalizeUrl($this->options['infos_url'], $parameters);

        try {
            $content = $this->doGetUserInformationRequest($url)->toArray(false);

            $response = $this->getUserResponse();
            $response->setData($content);
            $response->setResourceOwner($this);
            $response->setOAuthToken(new OAuthToken($accessToken));

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
            'authorization_url' => 'https://connect.ok.ru/oauth/authorize',
            'access_token_url' => 'https://api.ok.ru/oauth/token.do',
            'infos_url' => 'https://api.ok.ru/fb.do?method=users.getCurrentUser',

            'application_key' => null,
            'fields' => null,
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
