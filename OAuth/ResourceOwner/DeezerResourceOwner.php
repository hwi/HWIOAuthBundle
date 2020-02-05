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

use Symfony\Component\HttpFoundation\Request as HttpRequest;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Kieu Anh Tuan <passkey1510@gmail.com>
 */
class DeezerResourceOwner extends GenericOAuth2ResourceOwner
{
    /**
     * {@inheritdoc}
     */
    protected $paths = [
        'identifier' => 'id',
        'nickname' => 'name',
        'realname' => 'firstname',
        'email' => 'email',
        'firstname' => 'firstname',
        'lastname' => 'lastname',
        'profilepicture' => 'picture',
        'gender' => 'gender',
    ];

    /**
     * {@inheritdoc}
     */
    public function getAccessToken(HttpRequest $request, $redirectUri, array $extraParameters = [])
    {
        $extraParameters['app_id'] = $this->options['client_id'];
        $extraParameters['secret'] = $this->options['client_secret'];
        return parent::getAccessToken($request, $redirectUri, $extraParameters);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'authorization_url' => 'https://connect.deezer.com/oauth/auth.php',
            'access_token_url' => 'https://connect.deezer.com/oauth/access_token.php',
            'infos_url' => 'https://api.deezer.com/user/me',
            'use_bearer_authorization' => false,
        ]);
    }
}
