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
    protected $options = array(
        'authorization_url' => 'https://login.salesforce.com/services/oauth2/authorize',
        'access_token_url'  => 'https://login.salesforce.com/services/oauth2/token',
      
    );

    /**
     * {@inheritDoc}
     */
    protected $paths = array(
        'identifier' => 'user_id',
        'nickname'   => 'nick_name',
        'realname'   => 'nick_name',
        'email'      => 'email',
    );
    public function getUserInformation(array $accessToken, array $extraParameters = array())
    {


        $url = $this->normalizeUrl($accessToken['id'], array(
            'access_token' => $accessToken['access_token']
        ));
	
        $content = $this->doGetUserInformationRequest($url)->getContent();
	
        $response = $this->getUserResponse();
        $response->setResponse($content);
        $response->setResourceOwner($this);
        $response->setOAuthToken(new OAuthToken($accessToken));
	
        return $response;
    }
  
    /**
     * {@inheritDoc}
     */
    protected function doGetUserInformationRequest($url, array $parameters = array())
    {
	
     	
        $url = str_replace('access_token', 'oauth_token', $url);
	$url .= "&format=json";
	
        return $this->httpRequest($url);
    }
}
