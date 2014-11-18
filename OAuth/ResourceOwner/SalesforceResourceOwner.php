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

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Salesforce Resource Owner
 *
 * @author Tyler Pugh <tylerism@gmail.com>
 */
class SalesforceResourceOwner extends GenericOAuth2ResourceOwner
{
    /**
     * {@inheritDoc}
     */
    protected $paths = array(
        'identifier'     => 'user_id',
        'nickname'       => 'nick_name',
        'realname'       => 'nick_name',
        'email'          => 'email',
        'profilepicture' => 'photos.picture',
    );

    /**
     * {@inheritDoc}
     */
    public function getUserInformation(array $accessToken, array $extraParameters = array())
    {
        // SalesForce returns the infos_url in the OAuth Response Token
        $this->options['infos_url'] = $accessToken['id'];

        return parent::getUserInformation($accessToken, $extraParameters);
    }

    /**
     * {@inheritDoc}
     */
    protected function doGetUserInformationRequest($url, array $parameters = array())
    {
        // Salesforce requires format parameter in order for API to return json response
        $url = $this->normalizeUrl($url, array(
            'format' => $this->options['format']
        ));

        // Salesforce require to pass the OAuth token as 'oauth_token' instead of 'access_token'
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
            'authorization_url' => 'https://login.salesforce.com/services/oauth2/authorize',
            'access_token_url'  => 'https://login.salesforce.com/services/oauth2/token',

            // @see SalesforceResourceOwner::getUserInformation()
            'infos_url'         => null,

            // @see SalesforceResourceOwner::doGetUserInformationRequest()
            'format'            => 'json',
        ));
    }

}
