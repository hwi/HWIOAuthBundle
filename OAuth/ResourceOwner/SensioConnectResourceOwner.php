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

use HWI\Bundle\OAuthBundle\OAuth\Response\SensioConnectUserResponse;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * SensioConnectResourceOwner.
 *
 * @author Joseph Bielawski <stloyd@gmail.com>
 */
class SensioConnectResourceOwner extends GenericOAuth2ResourceOwner
{
    /**
     * {@inheritdoc}
     */
    protected function doGetUserInformationRequest($url, array $parameters = [])
    {
        return $this->httpRequest($url, null, ['Accept' => 'application/vnd.com.sensiolabs.connect+xml']);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'authorization_url' => 'https://connect.symfony.com/oauth/authorize',
            'access_token_url' => 'https://connect.symfony.com/oauth/access_token',
            'infos_url' => 'https://connect.symfony.com/api',

            'user_response_class' => SensioConnectUserResponse::class,

            'response_type' => 'code',

            'use_bearer_authorization' => false,
            'csrf' => true,
        ]);
    }
}
