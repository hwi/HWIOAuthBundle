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
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * @author Janne Savolainen <janne.savolainen@sempre.fi>
 */
class SpotifyResourceOwner extends GenericOAuth2ResourceOwner
{
    /**
     * {@inheritDoc}
     */
    protected $paths = array(
        'identifier'     => 'id',
        'nickname'       => 'id',
        'realname'       => 'display_name',
        'email'          => 'email'
    );

    /**
     * {@inheritDoc}
     */
    public function getUserInformation(array $accessToken = null, array $extraParameters = array())
    {
        $url = $this->normalizeUrl($this->options['infos_url'], array(
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
    protected function configureOptions(OptionsResolverInterface $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'authorization_url' => 'https://accounts.spotify.com/authorize',
            'access_token_url'  => 'https://accounts.spotify.com/api/token',
            'infos_url'         => 'https://api.spotify.com/v1/me'
        ));
    }
}