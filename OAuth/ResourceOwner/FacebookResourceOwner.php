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

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * FacebookResourceOwner.
 *
 * @author Geoffrey Bachelet <geoffrey.bachelet@gmail.com>
 */
class FacebookResourceOwner extends GenericOAuth2ResourceOwner
{
    /**
     * {@inheritdoc}
     */
    protected $paths = array(
        'identifier' => 'id',
        'nickname' => 'name',
        'firstname' => 'first_name',
        'lastname' => 'last_name',
        'realname' => 'name',
        'email' => 'email',
    );

    /**
     * {@inheritdoc}
     */
    public function getUserInformation(array $accessToken, array $extraParameters = array())
    {
        if ($this->options['appsecret_proof']) {
            $extraParameters['appsecret_proof'] = hash_hmac('sha256', $accessToken['access_token'], $this->options['client_secret']);
        }

        return parent::getUserInformation($accessToken, $extraParameters);
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizationUrl($redirectUri, array $extraParameters = array())
    {
        $extraOptions = array();
        if (isset($this->options['display'])) {
            $extraOptions['display'] = $this->options['display'];
        }

        if (isset($this->options['auth_type'])) {
            $extraOptions['auth_type'] = $this->options['auth_type'];
        }

        return parent::getAuthorizationUrl($redirectUri, array_merge($extraOptions, $extraParameters));
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessToken(Request $request, $redirectUri, array $extraParameters = array())
    {
        $parameters = array();
        if ($request->query->has('fb_source')) {
            $parameters['fb_source'] = $request->query->get('fb_source');
        }

        if ($request->query->has('fb_appcenter')) {
            $parameters['fb_appcenter'] = $request->query->get('fb_appcenter');
        }

        return parent::getAccessToken($request, $this->normalizeUrl($redirectUri, $parameters), $extraParameters);
    }

    /**
     * {@inheritdoc}
     */
    public function revokeToken($token)
    {
        $parameters = array(
            'client_id' => $this->options['client_id'],
            'client_secret' => $this->options['client_secret'],
        );

        $response = $this->httpRequest($this->normalizeUrl($this->options['revoke_token_url'], array('access_token' => $token)), $parameters, array(), 'DELETE');

        return 200 === $response->getStatusCode();
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'authorization_url' => 'https://www.facebook.com/v2.8/dialog/oauth',
            'access_token_url' => 'https://graph.facebook.com/v2.8/oauth/access_token',
            'revoke_token_url' => 'https://graph.facebook.com/v2.8/me/permissions',
            'infos_url' => 'https://graph.facebook.com/v2.8/me?fields=first_name,last_name,name,email',
            'use_commas_in_scope' => true,
            'display' => null,
            'auth_type' => null,
            'appsecret_proof' => false,
        ));

        $resolver
            ->setAllowedValues('display', array('page', 'popup', 'touch', null)) // @link https://developers.facebook.com/docs/reference/dialogs/#display
            ->setAllowedValues('auth_type', array('rerequest', null)) // @link https://developers.facebook.com/docs/reference/javascript/FB.login/
            ->setAllowedTypes('appsecret_proof', 'bool') // @link https://developers.facebook.com/docs/graph-api/securing-requests
        ;
    }
}
