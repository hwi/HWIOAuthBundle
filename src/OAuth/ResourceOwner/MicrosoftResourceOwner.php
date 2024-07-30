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
 * @author Tomasz Kierat <tomek.kierat@gmail.com>
 */
final class MicrosoftResourceOwner extends GenericOAuth2ResourceOwner
{
    public const TYPE = 'microsoft';

    /**
     * {@inheritdoc}
     */
    protected array $paths = [
        'identifier' => 'id',
        'nickname' => 'userPrincipalName',
        'realname' => 'displayName',
        'firstname' => 'givenName',
        'lastname' => 'surname',
        'email' => 'userPrincipalName'
    ];

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'authorization_url' => 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize',
            'access_token_url' => 'https://login.microsoftonline.com/common/oauth2/v2.0/token',
            'infos_url' => 'https://graph.microsoft.com/v1.0/me',

            'scope' => 'https://graph.microsoft.com/user.read',
        ]);
    }
}
