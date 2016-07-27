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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * CleverResourceOwner
 *
 * @author Matt Farmer <work@mattfarmer.net>
 */
class CleverResourceOwner extends GenericOAuth2ResourceOwner
{
    /**
     * {@inheritDoc}
     */
    protected $paths = array(
        'identifier' => 'data.id',
        'email' => 'data.email',
        'firstname' => 'data.name.first',
        'lastname' => 'data.name.last',
        'realname' => array(
            'data.name.first',
            'data.name.middle',
            'data.name.last',
        ),
    );

    /**
     * {@inheritDoc}
     */
    protected function configureOptions(OptionsResolverInterface $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'authorization_url'   => 'https://clever.com/oauth/authorize',
            'access_token_url'    => 'https://clever.com/oauth/tokens',
            'infos_url'           => 'https://api.clever.com/me',
        ));
    }

    /**
     * {@inheritDoc}
     */
    protected function doGetTokenRequest($url, array $parameters = array())
    {
        $authPrehash = $this->options['client_id'] . ':' . $this->options['client_secret'];
        $authHeader = 'Authorization: Basic ' . base64_encode($authPrehash);

        return $this->httpRequest(
            $url,
            http_build_query($parameters, '', '&'),
            array(
                $authHeader,
            )
        );
    }
}
