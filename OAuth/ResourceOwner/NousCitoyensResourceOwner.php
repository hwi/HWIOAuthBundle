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
 * NousCitoyensResourceOwner
 *
 * @author Aurélien David<adavid@jolicode.com>
 */
class NousCitoyensResourceOwner extends GenericOAuth2ResourceOwner
{
    /**
     * {@inheritDoc}
     */
    protected $paths = array(
        'identifier' => 'uid',
        'nickname'   => 'email',
        'realname'   => 'display_name',
        'email'      => 'email',
    );


    public function getAccessToken(Request $request, $redirectUri, array $extraParameters = array())
    {
        $parameters = array_merge(array(
            'appid'      => $this->options['client_id'],
            'secret'     => $this->options['client_secret'],
            'code'       => $request->query->get('code'),
            'grant_type' => 'authorization_code',
        ), $extraParameters);

        $response = $this->doGetTokenRequest($this->options['access_token_url'], $parameters);
        $content = $this->getResponseContent($response);

        // $this->validateResponseContent($content);

        return $response;
    }

    /**
     * {@inheritDoc}
     */
    public function getAuthorizationUrl($redirectUri, array $extraParameters = array())
    {
        $parameters = array_merge(array(
            'appid'         => $this->options['client_id'],
            'redirect_uri'  => $redirectUri,
            'response_type' => 'code',
            'scope'         => null,
            'state'         => '123',
        ), $extraParameters);

//        ksort($parameters); // i don't know why, but the order of the parameters REALLY matters
        return $this->normalizeUrl($this->options['authorization_url'], $parameters);
    }

    /**
     * {@inheritDoc}
     */
    protected function configureOptions(OptionsResolverInterface $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'authorization_url' => 'http://ws.preprod.nouscitoyens.fr/oauth/index/authorize',
            'access_token_url'  => 'http://ws.preprod.nouscitoyens.fr/oauth/index/token',
            'infos_url'         => '',
        ));
    }
}
