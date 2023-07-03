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
 * @author Andrea Quintino <andreaquin1990@gmail.com>
 */
final class KeycloakResourceOwner extends GenericOAuth2ResourceOwner
{
    public const TYPE = 'keycloak';

    protected array $paths = [
        'identifier' => 'sub',
        'nickname' => 'preferred_username',
        'firstname' => 'given_name',
        'lastname' => 'family_name',
        'realname' => 'name',
        'email' => 'email',
        'profilepicture' => 'picture',
    ];

    public function getAuthorizationUrl($redirectUri, array $extraParameters = [])
    {
        return parent::getAuthorizationUrl($redirectUri, array_merge([
          'approval_prompt' => $this->getOption('approval_prompt'),
          'kc_idp_hint' => $this->getOption('idp_hint'),
        ], $extraParameters));
    }

    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
          'protocol' => 'openid-connect',
          'scope' => 'openid email',
          'response_type' => 'code',
          'approval_prompt' => 'auto',
          'authorization_url' => '{keycloak_url}/auth',
          'access_token_url' => '{keycloak_url}/token',
          'infos_url' => '{keycloak_url}/userinfo',
          'idp_hint' => null,
        ]);

        $resolver->setRequired([
          'realm',
          'base_url',
        ]);

        $normalizer = function (Options $options, $value) {
            return str_replace(
                '{keycloak_url}',
                $options['base_url'].'/realms/'.$options['realm'].'/protocol/'.$options['protocol'],
                $value
            );
        };

        $resolver->setNormalizer('authorization_url', $normalizer);
        $resolver->setNormalizer('access_token_url', $normalizer);
        $resolver->setNormalizer('infos_url', $normalizer);
    }
}
