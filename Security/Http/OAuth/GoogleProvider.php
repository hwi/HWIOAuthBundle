<?php

/*
 * This file is part of the KnpOAuthBundle package.
 *
 * (c) KnpLabs <hello@knplabs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Knp\Bundle\OAuthBundle\Security\Http\OAuth;

use Knp\Bundle\OAuthBundle\Security\Http\OAuth\OAuthProvider;

use Symfony\Component\HttpFoundation\Request;

/**
 * GoogleProvider
 *
 * @author Geoffrey Bachelet <geoffrey.bachelet@gmail.com>
 */
class GoogleProvider extends OAuthProvider
{
    /**
     * {@inheritDoc}
     */
    protected $options = array(
        'authorization_url' => 'https://accounts.google.com/o/oauth2/auth',
        'access_token_url'  => 'https://accounts.google.com/o/oauth2/token',
        'infos_url'         => 'https://www.googleapis.com/oauth2/v1/userinfo',
        'username_path'     => 'name',
        'scope'             => 'userinfo.profile',
    );

    /**
     * {@inheritDoc}
     */
    public function getAccessToken(Request $request, array $extraParameters = array())
    {
        $parameters = array_merge($extraParameters, array(
            'code'          => $request->get('code'),
            'grant_type'    => 'authorization_code',
            'client_id'     => $this->getOption('client_id'),
            'client_secret' => $this->getOption('secret'),
            'redirect_uri'  => $this->getRedirectUri($request),
        ));

        $url = $this->getOption('access_token_url');
        $content = http_build_query($parameters);

        $response = $this->httpRequest($url, $content);
        $response = json_decode($response);

        if (isset($response['error'])) {
            throw new AuthenticationException(sprintf('OAuth error: "%s"', $response['error']));
        }

        return $response['access_token'];
    }
}