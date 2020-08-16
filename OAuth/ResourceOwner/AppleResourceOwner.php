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

use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use HWI\Bundle\OAuthBundle\Security\OAuthErrorHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * AppleResourceOwner.
 *
 * @author Geoffrey Bachelet <geoffrey.bachelet@gmail.com>
 * @author Josip Letica <leticajosip.09@gmail.com>
 */
class AppleResourceOwner extends GenericOAuth2ResourceOwner
{
    /**
     * {@inheritdoc}
     */
    protected $paths = [
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
            throw new \Exception('Undefined index id_token');
        }

        $jwt = self::jwt_decode($accessToken['id_token']);
        $data = json_decode(base64_decode($jwt), true);

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
            'client_secret' => $this->options['client_secret'],
            'redirect_uri' => $redirectUri,
        ], $extraParameters);

        $response = $this->doGetTokenRequest($this->options['access_token_url'], $parameters);
        $response = $this->getResponseContent($response);
        $this->validateResponseContent($response);
        $user_info = $request->request->get('user');
        $user_info = json_decode($user_info, true);

        if (null !== $user_info) {
            $response['firstName'] = $user_info['name']['firstName'];
            $response['lastName'] = $user_info['name']['lastName'];
        }

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function refreshAccessToken($refreshToken, array $extraParameters = [])
    {
        $parameters = [];
        $parameters['client_id'] = $this->options['client_id'];
        $parameters['client_secret'] = $this->options['client_secret'];

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
            'revoke_token_url' => '',
            'infos_url' => '',
            'use_commas_in_scope' => false,
            'display' => null,
            'scope' => 'name email',
            'appsecret_proof' => false,
            'response_mode' => 'form_post',
        ]);
    }

    private static function jwt_decode($id_token)
    {
        //// from http://stackoverflow.com/a/28748285/624544
        [, $jwt] = explode('.', $id_token, 3);

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
}
