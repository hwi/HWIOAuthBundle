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
use Symfony\Component\Security\Core\Exception\AuthenticationException;

final class PassageResourceOwner extends GenericOAuth2ResourceOwner
{
    public const TYPE = 'passage';

    /**
     * {@inheritdoc}
     */
    protected array $paths = [
        'identifier' => 'sub',
        'email' => 'email',
        'phone_number' => 'phone_number',
        'email_verified' => 'email_verified',
        'phone_number_verified' => 'phone_number_verified',
    ];

    /**
     * {@inheritdoc}
     */
    public function revokeToken($token)
    {
        if (!isset($this->options['revoke_token_url'])) {
            throw new AuthenticationException('OAuth error: "Method unsupported."');
        }

        $parameters = [
            'client_id' => $this->options['client_id'],
            'client_secret' => $this->options['client_secret'],
            'token' => $token,
        ];

        $response = $this->httpRequest($this->normalizeUrl($this->options['revoke_token_url']), $parameters, [], 'POST');

        return 200 === $response->getStatusCode();
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'authorization_url' => 'https://{sub_domain}.withpassage.com/authorize',
            'access_token_url' => 'https://{sub_domain}.withpassage.com/token',
            'revoke_token_url' => 'https://{sub_domain}.withpassage.com/revoke',
            'infos_url' => 'https://{sub_domain}.withpassage.com/userinfo',

            'use_commas_in_scope' => false,
            'scope' => 'openid email',
        ]);

        $resolver->setRequired([
            'sub_domain',
        ]);

        $normalizer = function (Options $options, $value) {
            return str_replace('{sub_domain}', $options['sub_domain'], $value);
        };

        $resolver
            ->setNormalizer('authorization_url', $normalizer)
            ->setNormalizer('access_token_url', $normalizer)
            ->setNormalizer('revoke_token_url', $normalizer)
            ->setNormalizer('infos_url', $normalizer)
        ;
    }
}
