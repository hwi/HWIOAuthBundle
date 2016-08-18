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
use Buzz\Message\RequestInterface as HttpRequestInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * LinkedinResourceOwner
 *
 * @author Guillaume Potier <guillaume@wisembly.com>
 */
class WunderlistResourceOwner extends GenericOAuth2ResourceOwner
{
    /**
     * {@inheritDoc}
     */
    protected $paths = array(
        'identifier'     => 'id',
        'nickname'       => 'name',
        'realname'       => 'name',
        'email'          => 'email',
    );

    /**
     * {@inheritDoc}
     */
    protected function doGetUserInformationRequest($url, array $parameters = array())
    {
        return $this->httpRequest($url, null, array(
            'X-Client-ID'       => $this->options['client_id'],
            'X-Access-Token'    => $parameters['access_token'],
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function getUserInformation(array $accessToken, array $extraParameters = array())
    {
        $content = $this->doGetUserInformationRequest($this->options['infos_url'], array('access_token' => $accessToken['access_token']));

        $response = $this->getUserResponse();
        $response->setResponse($content->getContent());

        $response->setResourceOwner($this);
        $response->setOAuthToken(new OAuthToken($accessToken));

        return $response;
    }

    /**
     * {@inheritDoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'authorization_url' => 'https://www.wunderlist.com/oauth/authorize',
            'access_token_url'  => 'https://www.wunderlist.com/oauth/access_token',
            'infos_url'         => 'https://a.wunderlist.com/api/v1/user',
        ));
    }
}
