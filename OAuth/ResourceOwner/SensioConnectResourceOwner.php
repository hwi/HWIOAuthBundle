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
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * SensioConnectResourceOwner
 *
 * @author Joseph Bielawski <stloyd@gmail.com>
 */
class SensioConnectResourceOwner extends GenericOAuth2ResourceOwner
{
    /**
     * {@inheritDoc}
     */
    protected function doGetTokenRequest($url, array $parameters = array())
    {
        return $this->httpRequest($this->options['access_token_url'], $parameters, array(), HttpRequestInterface::METHOD_POST);
    }

    /**
     * {@inheritDoc}
     */
    protected function doGetUserInformationRequest($url, array $parameters = array())
    {
        return $this->httpRequest($url, null, array('Accept: application/vnd.com.sensiolabs.connect+xml'));
    }

    /**
     * {@inheritDoc}
     */
    protected function configureOptions(OptionsResolverInterface $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'authorization_url'        => 'https://connect.sensiolabs.com/oauth/authorize',
            'access_token_url'         => 'https://connect.sensiolabs.com/oauth/access_token',
            'infos_url'                => 'https://connect.sensiolabs.com/api',

            'user_response_class'      => '\HWI\Bundle\OAuthBundle\OAuth\Response\SensioConnectUserResponse',

            'response_type'            => 'code',

            'use_bearer_authorization' => false,
        ));
    }
}
