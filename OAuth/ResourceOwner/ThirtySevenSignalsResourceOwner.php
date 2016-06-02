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
 * ThirtySevenSignalsResourceOwner (37signals).
 *
 * @author Richard van den Brand <richard@vandenbrand.org>
 */
class ThirtySevenSignalsResourceOwner extends GenericOAuth2ResourceOwner
{
    /**
     * {@inheritdoc}
     */
    protected $paths = array(
        'identifier' => 'identity.id',
        'nickname' => 'identity.email_address',
        'firstname' => 'identity.first_name',
        'lastname' => 'identity.last_name',
        'realname' => array('identity.last_name', 'identity.first_name'),
        'email' => 'identity.email_address',
    );

    /**
     * {@inheritdoc}
     */
    public function getAuthorizationUrl($redirectUri, array $extraParameters = array())
    {
        return parent::getAuthorizationUrl($redirectUri, array_merge(array('type' => 'web_server'), $extraParameters));
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessToken(Request $request, $redirectUri, array $extraParameters = array())
    {
        return parent::getAccessToken($request, $redirectUri, array_merge(array('type' => 'web_server'), $extraParameters));
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'authorization_url' => 'https://launchpad.37signals.com/authorization/new',
            'access_token_url' => 'https://launchpad.37signals.com/authorization/token',
            'infos_url' => 'https://launchpad.37signals.com/authorization.json',
        ));
    }
}
