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
 * WindowsLiveResourceOwner.
 *
 * @author Alexander <iam.asm89@gmail.com>
 */
class WindowsLiveResourceOwner extends GenericOAuth2ResourceOwner
{
    /**
     * {@inheritdoc}
     */
    protected $paths = [
        'identifier' => 'id',
        'nickname' => 'name',
        'realname' => 'name',
        'firstname' => 'first_name',
        'lastname' => 'last_name',
        'email' => 'emails.account', // requires 'wl.emails' scope
    ];

    /**
     * {@inheritdoc}
     */
    protected function doGetTokenRequest($url, array $parameters = [])
    {
        return parent::httpRequest($url, http_build_query($parameters, '', '&'));
    }

    /**
     * {@inheritdoc}
     */
    protected function httpRequest($url, $content = null, array $headers = [], $method = null)
    {
        // Skip the Content-Type header in GenericOAuth2ResourceOwner::httpRequest
        return AbstractResourceOwner::httpRequest($url, $content, $headers, $method);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'authorization_url' => 'https://login.live.com/oauth20_authorize.srf',
            'access_token_url' => 'https://login.live.com/oauth20_token.srf',
            'infos_url' => 'https://apis.live.net/v5.0/me',

            'scope' => 'wl.signin',
        ]);
    }
}
