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

use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * StackExchangeResourceOwner.
 *
 * @author Joseph Bielawski <stloyd@gmail.com>
 */
class StackExchangeResourceOwner extends GenericOAuth2ResourceOwner
{
    /**
     * {@inheritdoc}
     */
    protected $paths = array(
        'identifier' => 'items.0.user_id',
        'nickname' => 'items.0.display_name',
        'realname' => 'items.0.display_name',
        'profilepicture' => 'items.0.profile_image',
    );

    /**
     * {@inheritdoc}
     */
    public function getUserInformation(array $accessToken, array $extraParameters = array())
    {
        $parameters = array_merge(
           array($this->options['attr_name'] => $accessToken['access_token']),
           array('site' => $this->options['site'], 'key' => $this->options['key']),
           $extraParameters
        );

        $content = $this->doGetUserInformationRequest($this->normalizeUrl($this->options['infos_url'], $parameters));

        $response = $this->getUserResponse();
        $response->setData((string) $content->getBody());
        $response->setResourceOwner($this);
        $response->setOAuthToken(new OAuthToken($accessToken));

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setRequired(array(
            'key',
        ));

        $resolver->setDefaults(array(
            'authorization_url' => 'https://stackexchange.com/oauth',
            'access_token_url' => 'https://stackexchange.com/oauth/access_token',
            'infos_url' => 'https://api.stackexchange.com/2.0/me',

            'scope' => 'no_expiry',
            'site' => 'stackoverflow',
            'use_bearer_authorization' => false,
        ));
    }
}
