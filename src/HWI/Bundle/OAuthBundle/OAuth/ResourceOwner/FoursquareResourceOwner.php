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

use Buzz\Message\MessageInterface as HttpMessageInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * FoursquareResourceOwner
 *
 * @author Joseph Bielawski <stloyd@gmail.com>
 */
class FoursquareResourceOwner extends GenericOAuth2ResourceOwner
{
    /**
     * {@inheritDoc}
     */
    protected $paths = array(
        'identifier'     => 'response.user.id',
        'firstname'      => 'response.user.firstName',
        'lastname'       => 'response.user.lastName',
        'nickname'       => 'response.user.firstName',
        'realname'       => array('response.user.firstName', 'response.user.lastName'),
        'email'          => 'response.user.contact.email',
        'profilepicture' => 'response.user.photo',
    );

    /**
     * {@inheritDoc}
     */
    protected function getResponseContent(HttpMessageInterface $rawResponse)
    {
        $response = parent::getResponseContent($rawResponse);

        // Foursquare use quite custom response structure in case of error
        if (isset($response['meta']['errorType'])) {
            // Prevent to mark deprecated calls as errors
            if (200 == $response['meta']['code']) {
                $response['error'] = $response['meta']['errorType'];
                // Try to add some details of error if available
                if (isset($response['meta']['errorMessage'])) {
                    $response['error'] .= ' ' . $response['meta']['errorMessage'];
                } elseif (isset($response['meta']['errorDetail'])) {
                    $response['error'] .= ' ' . $response['meta']['errorDetail'];
                }
            }

            unset($response['meta']);
        }

        return $response;
    }

    /**
     * {@inheritDoc}
     */
    protected function doGetUserInformationRequest($url, array $parameters = array())
    {
        // Foursquare require to pass the 'v' ('version' = date in format 'YYYYMMDD') parameter when requesting API
        $url = $this->normalizeUrl($url, array(
            'v' => $this->options['version']
        ));

        // Foursquare require to pass the OAuth token as 'oauth_token' instead of 'access_token'
        $url = str_replace('access_token', 'oauth_token', $url);

        return $this->httpRequest($url);
    }

    /**
     * {@inheritDoc}
     */
    protected function configureOptions(OptionsResolverInterface $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'authorization_url'        => 'https://foursquare.com/oauth2/authenticate',
            'access_token_url'         => 'https://foursquare.com/oauth2/access_token',
            'infos_url'                => 'https://api.foursquare.com/v2/users/self',

            // @link https://developer.foursquare.com/overview/versioning
            'version'                  => '20121206',

            'use_bearer_authorization' => false,
        ));
    }
}
