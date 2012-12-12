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

use Symfony\Component\Security\Core\Exception\AuthenticationException,
    Symfony\Component\Security\Http\HttpUtils;

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
    protected $options = array(
        'authorization_url'   => 'https://connect.sensiolabs.com/oauth/authorize',
        'access_token_url'    => 'https://connect.sensiolabs.com/oauth/access_token',
        'infos_url'           => 'https://connect.sensiolabs.com/api',
        'scope'               => '',
        'user_response_class' => '\HWI\Bundle\OAuthBundle\OAuth\Response\SensioConnectUserResponse',
        'response_type'       => 'code',
    );

    /**
     * {@inheritDoc}
     */
    protected function doGetAccessTokenRequest($url, array $parameters = array())
    {
        return $this->httpRequest($this->getOption('access_token_url'), $parameters, array(), 'POST');
    }

    /**
     * {@inheritDoc}
     */
    protected function doGetUserInformationRequest($url, array $parameters = array())
    {
        return $this->httpRequest($url, null, array('Accept: application/vnd.com.sensiolabs.connect+xml'));
    }
}
