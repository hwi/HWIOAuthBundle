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
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;

/**
 * LineResourceOwner.
 *
 * @author Anthony AHMED <antho.ahmed@gmail.com>
 */
class LineResourceOwner extends GenericOAuth2ResourceOwner
{
    /**
     * {@inheritdoc}
     */
    protected $paths = [
        'identifier' => 'sub',
        'realname' => 'name',
        'email' => 'email',
    ];

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'access_token_url' => 'https://api.line.me/oauth2/v2.1/token',
            'attr_name' => 'oauth_token',
            'authorization_url' => 'https://access.line.me/oauth2/v2.1/authorize',
            'infos_url' => 'https://api.line.me/oauth2/v2.1/verify',
            'scope' => 'profile openid email',
            'use_bearer_authorization' => false,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getUserInformation(array $accessToken, array $extraParameters = [])
    {
        $parameters = [
            'client_id' => $this->options['client_id'],
            'id_token' => $accessToken['id_token'],
        ];

        $response = $this->httpRequest($this->normalizeUrl($this->options['infos_url']), $parameters, [], 'POST');

        $output = $this->getUserResponse();
        $output->setData(json_encode($this->getResponseContent($response)));
        $output->setResourceOwner($this);
        $output->setOAuthToken(new OAuthToken($accessToken));

        return $output;
    }
}
