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

use Buzz\Message\Response;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Buzz\Message\RequestInterface as HttpRequestInterface;

/**
 * Auth0ResourceOwner
 *
 * @author Hernan Rajchert <hrajchert@gmail.com>
 */
class Auth0ResourceOwner extends GenericOAuth2ResourceOwner
{
    /**
     * {@inheritDoc}
     */
    protected $paths = array(
        'identifier'     => 'user_id',
        'nickname'       => 'nickname',
        'realname'       => 'name',
        'email'          => 'email',
        'profilepicture' => 'picture',
    );

    protected function getAuthClient() {
        return base64_encode(json_encode(array(

                'name' => 'HWIOAuthBundle', 
                'version' => 'unknown',

                'environment' => array(
                    'name' => 'PHP', 
                    'version' => phpversion()
                )

            )));
    }

    protected function getRequestHeaders($extends = array()) {

        return array_merge($extends, array(
            'Auth0-Client' => $this->getAuthClient()
        ));
    }
    /**
     * {@inheritDoc}
     */
    protected function doGetTokenRequest($url, array $parameters = array())
    {

        $headers = $this->getRequestHeaders(array('Content-Type' => 'application/x-www-form-urlencoded'));

        return $this->httpRequest($url, http_build_query($parameters, '', '&'), $headers, HttpRequestInterface::METHOD_POST);
    }

    /**
     * {@inheritDoc}
     */
    protected function doGetUserInformationRequest($url, array $parameters = array())
    {

        $headers = $this->getRequestHeaders();

        return $this->httpRequest($url, http_build_query($parameters, '', '&'), $headers);
    }


    /**
     * {@inheritDoc}
     */
    protected function configureOptions(OptionsResolverInterface $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults(array(
            'authorization_url'   => '{base_url}/authorize?auth0Client=' . $this->getAuthClient(),
            'access_token_url'    => '{base_url}/oauth/token',
            'infos_url'           => '{base_url}/userinfo',
        ));

        $resolver->setRequired(array(
            'base_url',
        ));

        $normalizer = function (Options $options, $value) {
            return str_replace('{base_url}', $options['base_url'], $value);
        };

        $resolver->setNormalizers(array(
            'authorization_url' => $normalizer,
            'access_token_url'  => $normalizer,
            'infos_url'         => $normalizer,
        ));
    }
}
