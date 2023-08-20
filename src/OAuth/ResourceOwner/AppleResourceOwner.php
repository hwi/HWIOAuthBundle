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

use Firebase\JWT\JWT;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use HWI\Bundle\OAuthBundle\Security\OAuthErrorHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Geoffrey Bachelet <geoffrey.bachelet@gmail.com>
 * @author Josip Letica <leticajosip.09@gmail.com>
 * @author Sébastien Alfaiate <seb33300@hotmail.com>
 */
final class AppleResourceOwner extends GenericOAuth2ResourceOwner
{
    public const TYPE = 'apple';

    /**
     * {@inheritdoc}
     */
    protected array $paths = [
        'identifier' => 'sub',
        'firstname' => 'firstName',
        'lastname' => 'lastName',
        'email' => 'email',
    ];

    /**
     * {@inheritdoc}
     */
    public function getAuthorizationUrl($redirectUri, array $extraParameters = [])
    {
        return parent::getAuthorizationUrl($redirectUri, array_merge([
            'response_mode' => $this->options['response_mode'],
        ], $extraParameters));
    }

    /**
     * {@inheritdoc}
     */
    public function getUserInformation(array $accessToken, array $extraParameters = [])
    {
        if (!isset($accessToken['id_token'])) {
            throw new \InvalidArgumentException('Undefined index id_token');
        }

        $jwt = self::jwtDecode($accessToken['id_token']);
        $data = $jwt ? json_decode(base64_decode($jwt), true) : [];

        if (isset($accessToken['firstName'], $accessToken['lastName'])) {
            $data['firstName'] = $accessToken['firstName'];
            $data['lastName'] = $accessToken['lastName'];
        }

        $response = $this->getUserResponse();
        $response->setData(json_encode($data));
        $response->setResourceOwner($this);
        $response->setOAuthToken(new OAuthToken($accessToken));

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessToken(Request $request, $redirectUri, array $extraParameters = [])
    {
        OAuthErrorHandler::handleOAuthError($request);

        $parameters = array_merge([
            'code' => $request->request->get('code'),
            'grant_type' => 'authorization_code',
            'client_id' => $this->options['client_id'],
            'client_secret' => $this->getClientSecret(),
            'redirect_uri' => $redirectUri,
        ], $extraParameters);

        $response = $this->doGetTokenRequest($this->options['access_token_url'], $parameters);
        $response = $this->getResponseContent($response);

        $this->validateResponseContent($response);

        $userInfo = $request->request->get('user');
        if (null !== $userInfo) {
            $userInfo = json_decode($userInfo, true, 512, \JSON_THROW_ON_ERROR);
            if (isset($userInfo['name'])) {
                $response['firstName'] = $userInfo['name']['firstName'];
                $response['lastName'] = $userInfo['name']['lastName'];
            }
        }

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function refreshAccessToken($refreshToken, array $extraParameters = [])
    {
        $parameters = [
            'client_id' => $this->options['client_id'],
            'client_secret' => $this->options['client_secret'],
        ];

        return parent::refreshAccessToken($refreshToken, array_merge($parameters, $extraParameters));
    }

    /**
     * {@inheritdoc}
     */
    public function handles(Request $request)
    {
        return $request->request->has('code');
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'authorization_url' => 'https://appleid.apple.com/auth/authorize',
            'access_token_url' => 'https://appleid.apple.com/auth/token',
            'infos_url' => '',
            'use_commas_in_scope' => false,
            'display' => null,
            'scope' => 'name email',
            'appsecret_proof' => false,
            'response_mode' => 'form_post',
            'auth_key' => null,
            'key_id' => null,
            'team_id' => null,
        ]);
    }

    private static function jwtDecode(string $idToken)
    {
        // from http://stackoverflow.com/a/28748285/624544
        [, $jwt] = explode('.', $idToken, 3);

        // if the token was urlencoded, do some fixes to ensure that it is valid base64 encoded
        $jwt = str_replace(['-', '_'], ['+', '/'], $jwt);

        // complete token if needed
        switch (\strlen($jwt) % 4) {
            case 0:
                break;
            case 2:
            case 3:
                $jwt .= '=';
                break;
            default:
                throw new \InvalidArgumentException('Invalid base64 format sent back');
        }

        return $jwt;
    }

    private function getClientSecret(): string
    {
        if ('auto' !== $this->options['client_secret']) {
            return $this->options['client_secret'];
        }

        if (!isset($this->options['auth_key'], $this->options['key_id'], $this->options['team_id'])) {
            throw new \InvalidArgumentException('Options "auth_key", "key_id" and "team_id" must be defined to use automatic "client_secret" generation.');
        }

        if (!class_exists(JWT::class)) {
            throw new \RuntimeException('PHP-JWT library is required to use automatic "client_secret" generation. Please try "composer require firebase/php-jwt".');
        }

        $payload = [
            'iss' => $this->options['team_id'],
            'iat' => time(),
            'exp' => time() + 600,
            'aud' => 'https://appleid.apple.com',
            'sub' => $this->options['client_id'],
        ];

        return JWT::encode($payload, $this->options['auth_key'], 'ES256', $this->options['key_id']);
    }
}
