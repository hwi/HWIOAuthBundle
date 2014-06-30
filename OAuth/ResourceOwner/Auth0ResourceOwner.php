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

    /**
     * {@inheritDoc}
     */
    protected function doGetTokenRequest($url, array $parameters = array())
    {

        $headers = array(
            'content-type' => 'application/x-www-form-urlencoded'
        );
        // implode('', $content)
        return $this->httpRequest($url, http_build_query($parameters, '', '&'), $headers, HttpRequestInterface::METHOD_POST);
    }


    /**
     * {@inheritDoc}
     */
    protected function configureOptions(OptionsResolverInterface $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults(array(
            'authorization_url'   => 'https://%%domain%%/authorize',
            'access_token_url'    => 'https://%%domain%%/oauth/token',
            'domain'              => 'example.auth0.com',
            'infos_url'           => 'https://%%domain%%/userinfo',
        ));

        $domainReplacer = function ($options, $value) {
            if (!$value) {
                return null;
            }

            return str_replace('%%domain%%', $options['domain'], $value);
        };

        $resolver->setNormalizers(array(
            'authorization_url' => $domainReplacer,
            'access_token_url' => $domainReplacer,
            'infos_url' => $domainReplacer,
        ));
    }
}
