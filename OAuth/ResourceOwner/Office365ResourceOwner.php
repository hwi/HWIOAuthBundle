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

class Office365ResourceOwner extends GenericOAuth2ResourceOwner
{
    /**
     * @var array
     */
    protected $paths = array(
        'identifier' => 'id',
        'email' => 'mail',
        'realname' => 'displayName',
        'firstname' => 'givenName',
        'lastname' => 'surname',
    );

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'authorization_url' => 'https://login.microsoftonline.com/common/oauth2/authorize',
            'access_token_url' => 'https://login.microsoftonline.com/common/oauth2/token',
            'infos_url' => 'https://graph.microsoft.com/v1.0/me',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessToken(Request $request, $redirectUri, array $extraParameters = array())
    {
        $extraParameters = array_merge(array(
            'resource' => 'https://graph.microsoft.com',
        ), $extraParameters);

        return parent::getAccessToken($request, $redirectUri, $extraParameters);
    }
}
