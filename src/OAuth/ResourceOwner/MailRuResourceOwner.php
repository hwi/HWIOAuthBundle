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
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * @author Gaponov Igor <jiminy96@gmail.com>
 */
final class MailRuResourceOwner extends GenericOAuth2ResourceOwner
{
    public const TYPE = 'mailru';

    /**
     * {@inheritdoc}
     */
    protected array $paths = [
        'identifier' => 'uid',
        'nickname' => 'nick',
        'realname' => 'nick',
        'email' => 'email',
    ];

    /**
     * {@inheritdoc}
     */
    public function getUserInformation(array $accessToken, array $extraParameters = [])
    {
        $params = [
            'app_id' => $this->options['client_id'],
            'method' => 'users.getInfo',
            'secure' => '1',
            'session_key' => $accessToken['access_token'],
        ];

        $params['sig'] = md5(vsprintf('app_id=%smethod=%ssecure=%ssession_key=%s', $params).$this->options['client_secret']);

        $url = $this->normalizeUrl($this->options['infos_url'], $params);

        try {
            $content = $this->doGetUserInformationRequest($url)->toArray(false);
            if (isset($content[0])) {
                $content = (array) $content[0];
            }

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
            'authorization_url' => 'https://connect.mail.ru/oauth/authorize',
            'access_token_url' => 'https://connect.mail.ru/oauth/token',
            'infos_url' => 'http://www.appsmail.ru/platform/api',
        ]);
    }
}
