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

use Buzz\Message\RequestInterface as HttpRequestInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * LeadAuthResourceOwner
 *
 * @author Kurakin Oleksandr <kurakin@lead-auth.com>
 */
class LeadAuthResourceOwner extends GenericOAuth2ResourceOwner
{
    /**
     * {@inheritDoc}
     */
    protected $paths = array(
        'identifier'     => '_id',
        'nickname'       => 'username',
        'realname'       => 'profile.display_name',
        'email'          => 'profile.email.value',
        'profilepicture' => 'profile.photo',
    );

    /**
     * {@inheritDoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'authorization_url' => 'https://{account}.lead-auth-local.com:3000/authorize',
            'access_token_url'  => 'https://{account}.lead-auth-local.com:3000/api/token',
            'infos_url'         => 'https://{account}.lead-auth-local.com:3000/api/users/me',
        ));

        $resolver->setRequired(array(
            'account',
        ));

        $normalizer = function (Options $options, $value) {
            return str_replace('{account}', $options['account'], $value);
        };

        // Symfony <2.6 BC
        if (method_exists($resolver, 'setNormalizer')) {
            $resolver
                ->setNormalizer('authorization_url', $normalizer)
                ->setNormalizer('access_token_url', $normalizer)
                ->setNormalizer('infos_url', $normalizer)
            ;
        } else {
            $resolver->setNormalizers(array(
                'authorization_url' => $normalizer,
                'access_token_url'  => $normalizer,
                'infos_url'         => $normalizer,
            ));
        }
    }
}
