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

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Latysh <altynbek.usenov@gmail.com>
 */
final class AmazonCognitoResourceOwner extends GenericOAuth2ResourceOwner
{
    public const TYPE = 'amazon_cognito';

    protected array $paths = [
        'identifier' => 'sub',
        'firstname' => 'given_name',
        'lastname' => 'family_name',
        'email' => 'email',
        'nickname' => 'nickname',
        'realname' => 'name',
    ];

    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'authorization_url' => '{base_url}/oauth2/authorize',
            'access_token_url' => '{base_url}/oauth2/token',
            'revoke_token_url' => '{base_url}/oauth2/revoke',
            'infos_url' => '{base_url}/oauth2/userInfo',
        ]);

        $resolver->setRequired([
            'region',
            'domain',
        ]);

        $normalizer = function (Options $options, $value) {
            $baseUrl = \sprintf('https://%s.auth.%s.amazoncognito.com', $options['domain'], $options['region']);

            return str_replace('{base_url}', $baseUrl, $value);
        };

        $resolver
            ->setNormalizer('authorization_url', $normalizer)
            ->setNormalizer('access_token_url', $normalizer)
            ->setNormalizer('revoke_token_url', $normalizer)
            ->setNormalizer('infos_url', $normalizer);
    }
}
