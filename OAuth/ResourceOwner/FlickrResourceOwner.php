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
 * FlickrResourceOwner
 *
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
 */
class FlickrResourceOwner extends GenericOAuth1ResourceOwner
{
    /**
     * {@inheritDoc}
     */
    protected $paths = array(
        'identifier' => 'user_nsid',
        'nickname'   => 'username',
        'realname'   => 'fullname',
    );

    /**
     * {@inheritDoc}
     */
    public function getAuthorizationUrl($redirectUri, array $extraParameters = array())
    {
        $token = $this->getRequestToken($redirectUri, $extraParameters);

        $params = array(
            'oauth_token'    => $token['oauth_token'],
            'perms'          => $this->options['perms'],
            'nojsoncallback' => 1,
        );

        return $this->normalizeUrl($this->options['authorization_url'], $params);
    }

    /**
     * {@inheritDoc}
     */
    public function getUserInformation(array $accessToken, array $extraParameters = array())
    {
        $response = $this->getUserResponse();
        $response->setResponse($accessToken);
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
            'authorization_url' => 'http://www.flickr.com/services/oauth/authorize',
            'request_token_url' => 'http://www.flickr.com/services/oauth/request_token',
            'access_token_url'  => 'http://www.flickr.com/services/oauth/access_token',

            // Flickr don't use `infos_url`
            'infos_url'         => null,

            'perms'             => 'read',
        ));
    }
}
