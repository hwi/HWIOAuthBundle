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
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;

/**
 * WordpressResourceOwner
 *
 * @author Joseph Bielawski <stloyd@gmail.com>
 * @author ahmed-hamdy90 <ahmedhamdy20@gmail.com>
 */
class WordpressResourceOwner extends GenericOAuth2ResourceOwner
{
    /**
     * {@inheritDoc}
     */
    protected $paths = array(
        'identifier'     => 'ID',
        'nickname'       => 'username',
        'realname'       => 'display_name',
        'email'          => 'email',
        'profilepicture' => 'avatar_URL',
    );
    
    /**
     * {@inheritDoc}
     */
    protected function doGetUserInformationRequest($url, array $parameters = array())
    {
        $accessToken = $parameters['accessToken'];
        $headers = array(
            0 => 'authorization: Bearer '.$accessToken,
        );
        return $this->httpRequest($url,null,$headers);
    }
    
    /**
     * {@inheritDoc}
     */
    public function getUserInformation(array $accessToken, array $extraParameters = array())
    {
        $url = $this->normalizeUrl($this->options['infos_url'], array(
            'access_token' => $accessToken['access_token']
        ));

        $parameters = array(
            'accessToken' => $accessToken['access_token'],
        );
        
        $content = $this->doGetUserInformationRequest($url,$parameters)->getContent();

        $response = $this->getUserResponse();
        $response->setResponse($content);
        $response->setResourceOwner($this);
        $response->setOAuthToken(new OAuthToken($accessToken));
     
        return $response;
    }

    /**
     * {@inheritDoc}
     */
    protected function configureOptions(OptionsResolverInterface $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'authorization_url' => 'https://public-api.wordpress.com/oauth2/authorize',
            'access_token_url'  => 'https://public-api.wordpress.com/oauth2/token',
            'infos_url'         => 'https://public-api.wordpress.com/rest/v1/me',
        ));
    }
}
