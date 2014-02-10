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
 * WordpressResourceOwner
 *
 * @author Joseph Bielawski <stloyd@gmail.com>
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
    public function getUserInformation(array $accessToken, array $extraParameters = array())
    {
        $content = $this->httpRequest($this->normalizeUrl($this->options['infos_url']), null, array('Authorization: Bearer '.$accessToken['access_token']))->getContent();

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
