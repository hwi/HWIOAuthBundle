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

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Tom <tomilett@gmail.com>
 * @author Alexander <iam.asm89@gmail.com>
 */
final class YahooResourceOwner extends GenericOAuth2ResourceOwner
{
    public const TYPE = 'yahoo';

    /**
     * {@inheritdoc}
     */
    protected array $paths = [
        'identifier' => 'sub',
        'nickname' => 'given_name',
        'realname' => 'name',
        'email' => 'email',
        'firstname' => 'given_name',
        'lastname' => 'family_name',
    ];

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'authorization_url' => 'https://api.login.yahoo.com/oauth2/request_auth',
            'request_token_url' => 'https://api.login.yahoo.com/oauth2/get_token',
            'access_token_url' => 'https://api.login.yahoo.com/oauth2/get_token',
            'infos_url' => 'https://api.login.yahoo.com/openid/v1/userinfo',

            'realm' => 'yahooapis.com',
        ]);
    }
}
