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

/**
 * DropboxResourceOwner
 *
 * @author Jamie Sutherland<me@jamiesutherland.com>
 */
class DropboxResourceOwner extends GenericOAuth1ResourceOwner
{
    /**
     * {@inheritDoc}
     */
    protected $options = array(
        'authorization_url'   => 'https://api.dropbox.com/1/oauth/authorize',
        'request_token_url'   => 'https://api.dropbox.com/1/oauth/request_token',
        'access_token_url'    => 'https://api.dropbox.com/1/oauth/access_token',
        'infos_url'           => 'https://api.dropbox.com/1/account/info',

        'signature_method'    => 'PLAINTEXT',
    );

    /**
     * {@inheritDoc}
     */
    protected $paths = array(
        'identifier' => 'uid',
        'nickname'   => 'email',
        'realname'   => 'display_name',
    );

    /**
     * {@inheritDoc}
     */
    public function getAuthorizationUrl($redirectUri, array $extraParameters = array())
    {
        $token = $this->getRequestToken($redirectUri, $extraParameters);

        return $this->normalizeUrl($this->getOption('authorization_url'), array('oauth_token' => $token['oauth_token'], 'oauth_callback' => $redirectUri));
    }

    /**
     * {@inheritDoc}
     */
    public function getUserInformation($accessToken, array $extraParameters = array())
    {
        $extraParameters = array_merge(array('oauth_signature_method' => $this->getOption('signature_method')), $extraParameters);

        return parent::getUserInformation($accessToken, $extraParameters);
    }

    /**
     * {@inheritDoc}
     */
    public function getAccessToken(Request $request, $redirectUri, array $extraParameters = array())
    {
        $extraParameters = array_merge(array('oauth_signature_method' => $this->getOption('signature_method')), $extraParameters);

        return parent::getAccessToken($request, $redirectUri, $extraParameters);
    }

    /**
     * {@inheritDoc}
     */
    protected function getRequestToken($redirectUri, array $extraParameters = array())
    {
        $extraParameters = array_merge(array('oauth_signature_method' => $this->getOption('signature_method')), $extraParameters);

        return parent::getRequestToken($redirectUri, $extraParameters);
    }
}
