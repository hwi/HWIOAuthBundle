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

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * YoutubeResourceOwner
 *
 * @author Gennady Telegin <gtelegin@gmail.com>
 */
class YoutubeResourceOwner extends GenericOAuth2ResourceOwner
{
    /**
     * {@inheritDoc}
     */
    protected $paths = array(
        'identifier'     => 'items.0.id',
        'nickname'       => 'items.0.snippet.title',
        'realname'       => 'items.0.snippet.title',
        'email'          => 'email',
        'profilepicture' => 'items.0.snippet.thumbnails.high.url',
    );

    /**
     * {@inheritDoc}
     */
    public function getAuthorizationUrl($redirectUri, array $extraParameters = array())
    {
        return parent::getAuthorizationUrl($redirectUri, array_merge(array(
            'access_type'             => $this->options['access_type'],
            'approval_prompt'         => $this->options['approval_prompt'],
            'request_visible_actions' => $this->options['request_visible_actions'],
            'hd'                      => $this->options['hd'],
            'prompt'                  => $this->options['prompt']
        ), $extraParameters));
    }

    /**
     * {@inheritDoc}
     */
    protected function setupOptions(OptionsResolver $resolver)
    {
        parent::setupOptions($resolver);

        $resolver->setDefaults(array(
            'authorization_url'       => 'https://accounts.google.com/o/oauth2/auth',
            'access_token_url'        => 'https://accounts.google.com/o/oauth2/token',
            'revoke_token_url'        => 'https://accounts.google.com/o/oauth2/revoke',
            'infos_url'               => 'https://www.googleapis.com/youtube/v3/channels?part=id,snippet&mine=true',
            'scope'                   => 'https://www.googleapis.com/auth/youtube.readonly',

            'access_type'             => null,
            'approval_prompt'         => null,
            'display'                 => null,
            // Identifying a particular hosted domain account to be accessed (for example, 'mycollege.edu')
            'hd'                      => null,
            'login_hint'              => null,
            'prompt'                  => null,
            'request_visible_actions' => null,
        ));

        $resolver->setAllowedValues(array(
            // @link https://developers.google.com/accounts/docs/OAuth2WebServer#offline
            'access_type'     => array('online', 'offline', null),
            // sometimes we need to force for approval prompt (e.g. when we lost refresh token)
            'approval_prompt' => array('force', 'auto', null),
            // @link https://developers.google.com/accounts/docs/OAuth2Login#authenticationuriparameters
            'display'         => array('page', 'popup', 'touch', 'wap', null),
            'login_hint'      => array('email address', 'sub', null),
            'prompt'          => array(null, 'consent', 'select_account', null),
        ));
    }
}
