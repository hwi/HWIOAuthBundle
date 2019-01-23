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

use HWI\Bundle\OAuthBundle\OAuth\Response\LinkedinUserResponse;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * LinkedinResourceOwner.
 *
 * @author Francisco Facioni <fran6co@gmail.com>
 * @author Joseph Bielawski <stloyd@gmail.com>
 */
class LinkedinResourceOwner extends GenericOAuth2ResourceOwner
{
    /**
     * {@inheritdoc}
     */
    protected $paths = array(
        'identifier' => 'id',
        'nickname' => 'emailAddress',
        'firstname' => 'firstName',
        'lastname' => 'lastName',
        'email' => 'emailAddress',
        'profilepicture' => 'profilePicture',
    );

    /**
     * {@inheritdoc}
     */
    public function getUserInformation(array $accessToken, array $extraParameters = array())
    {
        $response = parent::getUserInformation($accessToken, $extraParameters);

        $responseData = $response->getData();
        // The user info returned by /me doesn't contain the email so we make an extra request to fetch it
        $content = $this->httpRequest(
            $this->normalizeUrl($this->options['email_url'], $extraParameters),
            null,
            array('Authorization' => 'Bearer '.$accessToken['access_token'])
        );

        $emailResponse = $this->getResponseContent($content);
        if (isset($emailResponse['elements']) && count($emailResponse['elements']) > 0) {
            $responseData['emailAddress'] = $emailResponse['elements'][0]['handle~']['emailAddress'];
        }
        // errors not handled because I don't see any relevant thing to do with them

        $response->setData($responseData);

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    protected function doGetTokenRequest($url, array $parameters = array())
    {
        return $this->httpRequest($this->normalizeUrl($url, $parameters), null, array(), 'POST');
    }

    /**
     * {@inheritdoc}
     */
    protected function httpRequest($url, $content = null, array $headers = [], $method = null)
    {
        // Linkedin v2 API is supposed to require Content-Type: application/json but it works without
        // and request to get the access token doesn't seems to work with Content-Type: application/json
        // so we don't put any Content-Type header.
        // Skip the Content-Type header in GenericOAuth2ResourceOwner::httpRequest
        return AbstractResourceOwner::httpRequest($url, $content, $headers, $method);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'scope' => 'r_liteprofile r_emailaddress',
            'authorization_url' => 'https://www.linkedin.com/oauth/v2/authorization',
            'access_token_url' => 'https://www.linkedin.com/oauth/v2/accessToken',
            'infos_url' => 'https://api.linkedin.com/v2/me?projection=(id,firstName,lastName,profilePicture(displayImage~:playableStreams))',
            'email_url' => 'https://api.linkedin.com/v2/emailAddress?q=members&projection=(elements*(handle~))',

            'user_response_class' => LinkedinUserResponse::class,

            'csrf' => true,

            'use_bearer_authorization' => true,
        ));
    }
}
