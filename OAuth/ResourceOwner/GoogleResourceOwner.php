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

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * GoogleResourceOwner
 *
 * @author Geoffrey Bachelet <geoffrey.bachelet@gmail.com>
 * @author Alexander <iam.asm89@gmail.com>
 */
class GoogleResourceOwner extends GenericOAuth2ResourceOwner
{
    /**
     * {@inheritDoc}
     */
    protected $paths = array(
        'identifier'     => 'id',
        'nickname'       => 'name',
        'realname'       => 'name',
        'firstname'      => 'given_name',
        'lastname'       => 'family_name',
        'email'          => 'email',
        'profilepicture' => 'picture',
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
    public function revokeToken($token)
    {
        $response = $this->httpRequest($this->normalizeUrl($this->options['revoke_token_url'], array('token' => $token)));

        return 200 === $response->getStatusCode();
    }

    /**
     * {@inheritDoc}
     */
    protected function configureOptions(OptionsResolverInterface $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'authorization_url'       => 'https://accounts.google.com/o/oauth2/auth',
            'access_token_url'        => 'https://accounts.google.com/o/oauth2/token',
            'revoke_token_url'        => 'https://accounts.google.com/o/oauth2/revoke',
            'infos_url'               => 'https://www.googleapis.com/oauth2/v1/userinfo',

            'scope'                   => 'https://www.googleapis.com/auth/userinfo.profile',

            'access_type'             => null,
            'approval_prompt'         => null,
            'display'                 => null,
            // Identifying a particular hosted domain account to be accessed (for example, 'mycollege.edu')
            'hd'                      => null,
            'login_hint'              => null,
            'prompt'                  => null,
            'request_visible_actions' => null,
        ));

        if (method_exists($resolver, 'setDefined')) {
            $resolver
                // @link https://developers.google.com/accounts/docs/OAuth2WebServer#offline
                ->setAllowedValues('access_type', array('online', 'offline', null))
                // sometimes we need to force for approval prompt (e.g. when we lost refresh token)
                ->setAllowedValues('approval_prompt', array('force', 'auto', null))
                // @link https://developers.google.com/accounts/docs/OAuth2Login#authenticationuriparameters
                ->setAllowedValues('display', array('page', 'popup', 'touch', 'wap', null))
                ->setAllowedValues('login_hint', array('email address', 'sub', null))
                ->setAllowedValues('prompt', array('consent', 'select_account', null))
            ;
        } else {
            $resolver->setAllowedValues(array(
                'access_type'     => array('online', 'offline', null),
                'approval_prompt' => array('force', 'auto', null),
                'display'         => array('page', 'popup', 'touch', 'wap', null),
                'login_hint'      => array('email address', 'sub', null),
                'prompt'          => array('consent', 'select_account', null),
            ));
        }
    }
}
