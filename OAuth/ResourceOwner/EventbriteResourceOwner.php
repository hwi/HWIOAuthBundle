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
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * EventbriteResourceOwner
 *
 * @author Joseph Bielawski <stloyd@gmail.com>
 */
class EventbriteResourceOwner extends GenericOAuth2ResourceOwner
{
    /**
     * {@inheritDoc}
     */
    protected $paths = array(
        'identifier' => 'user.user_id',
        'nickname'   => 'user.first_name',
        'realname'   => array('user.first_name', 'user.last_name'),
        'email'      => 'email',
    );

    /**
     * {@inheritDoc}
     */
    public function getUserInformation(array $accessToken, array $extraParameters = array())
    {
        $url = $this->normalizeUrl($this->getOption('infos_url'), array(
            'access_token' => $accessToken['access_token']
        ));

        $content = $this->httpRequest($url, null, array('Authorization: Bearer '.$accessToken['access_token']))->getContent();

        $response = $this->getUserResponse();
        $response->setResponse($content);
        $response->setResourceOwner($this);
        $response->setOAuthToken(new OAuthToken($accessToken));

        return $response;
    }

    /**
     * {@inheritDoc}
     */
    protected function doGetTokenRequest($url, array $parameters = array())
    {
        return $this->httpRequest($url, http_build_query($parameters, '', '&'), array(), HttpRequestInterface::METHOD_POST);
    }

    /**
     * {@inheritDoc}
     */
    protected function configureOptions(OptionsResolverInterface $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'authorization_url' => 'https://www.eventbrite.com/oauth/authorize',
            'access_token_url'  => 'https://www.eventbrite.com/oauth/token',
            'infos_url'         => 'https://www.eventbrite.com/json/user_get',
        ));
    }
}
