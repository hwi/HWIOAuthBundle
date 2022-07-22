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
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @author Joseph Bielawski <stloyd@gmail.com>
 */
final class FoursquareResourceOwner extends GenericOAuth2ResourceOwner
{
    public const TYPE = 'foursquare';

    /**
     * {@inheritdoc}
     */
    protected array $paths = [
        'identifier' => 'response.user.id',
        'firstname' => 'response.user.firstName',
        'lastname' => 'response.user.lastName',
        'nickname' => 'response.user.firstName',
        'realname' => ['response.user.firstName', 'response.user.lastName'],
        'email' => 'response.user.contact.email',
        'profilepicture' => 'response.user.photo',
    ];

    /**
     * {@inheritdoc}
     */
    protected function getResponseContent(ResponseInterface $rawResponse): array
    {
        $response = parent::getResponseContent($rawResponse);

        // Foursquare use quite custom response structure in case of error
        if (isset($response['meta']['errorType'])) {
            // Prevent to mark deprecated calls as errors
            if (200 === (int) $response['meta']['code']) {
                $response['error'] = $response['meta']['errorType'];
                // Try to add some details of error if available
                if (isset($response['meta']['errorMessage'])) {
                    $response['error'] .= ' '.$response['meta']['errorMessage'];
                } elseif (isset($response['meta']['errorDetail'])) {
                    $response['error'] .= ' '.$response['meta']['errorDetail'];
                }
            }

            unset($response['meta']);
        }

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    protected function doGetUserInformationRequest($url, array $parameters = [])
    {
        // Foursquare require to pass the 'v' ('version' = date in format 'YYYYMMDD') parameter when requesting API
        $url = $this->normalizeUrl($url, [
            'v' => $this->options['version'],
        ]);

        // Foursquare require to pass the OAuth token as 'oauth_token' instead of 'access_token'
        $url = str_replace('access_token', 'oauth_token', $url);

        return $this->httpRequest($url);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'authorization_url' => 'https://foursquare.com/oauth2/authenticate',
            'access_token_url' => 'https://foursquare.com/oauth2/access_token',
            'infos_url' => 'https://api.foursquare.com/v2/users/self',

            // @link https://developer.foursquare.com/overview/versioning
            'version' => '20121206',

            'use_bearer_authorization' => false,
        ]);
    }
}
