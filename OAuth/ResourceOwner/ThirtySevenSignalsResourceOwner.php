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
 * ThirtySevenSignalsResourceOwner (37signals)
 *
 * @author Richard van den Brand <richard@vandenbrand.org>
 */
class ThirtySevenSignalsResourceOwner extends GenericOAuth2ResourceOwner
{
    /**
     * {@inheritDoc}
     */
    protected $options = array(
        'authorization_url'   => 'https://launchpad.37signals.com/authorization/new',
        'access_token_url'    => 'https://launchpad.37signals.com/authorization/token',
        'infos_url'           => 'https://launchpad.37signals.com/authorization.json',
        'scope'               => '',
        'user_response_class' => '\HWI\Bundle\OAuthBundle\OAuth\Response\AdvancedPathUserResponse',
    );

    /**
     * {@inheritDoc}
     */
    protected $paths = array(
        'identifier'     => 'identity.id',
        'nickname'       => 'identity.email_address',
        'realname'       => 'identity.last_name',
        'firstName'      => 'identity.first_name'
    );

    public function getAuthorizationUrl($redirectUri, array $extraParameters = array())
    {
        $mergedParameters = array_merge(
            array(
                'type' => 'web_server',
            ),
            $extraParameters
        );

        return parent::getAuthorizationUrl($redirectUri, $mergedParameters);
    }

    public function getAccessToken(Request $request, $redirectUri, array $extraParameters = array())
    {
        $mergedParameters = array_merge(
            array(
                'type' => 'web_server',
            ),
            $extraParameters
        );
        
        return parent::getAccessToken($request, $redirectUri, $mergedParameters);
    }
}
