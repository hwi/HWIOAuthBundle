<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\OAuth\ResourceOwner;

use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
 */
final class FlickrResourceOwner extends GenericOAuth1ResourceOwner
{
    public const TYPE = 'flickr';

    /**
     * {@inheritdoc}
     */
    protected array $paths = [
        'identifier' => 'user_nsid',
        'nickname' => 'username',
        'realname' => 'fullname',
    ];

    /**
     * {@inheritdoc}
     */
    public function getAuthorizationUrl($redirectUri, array $extraParameters = [])
    {
        $token = $this->getRequestToken($redirectUri, $extraParameters);

        return $this->normalizeUrl($this->options['authorization_url'], [
            'oauth_token' => $token['oauth_token'],
            'perms' => $this->options['perms'],
            'nojsoncallback' => 1,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getUserInformation(array $accessToken, array $extraParameters = [])
    {
        $response = $this->getUserResponse();
        $response->setData($accessToken);
        $response->setResourceOwner($this);
        $response->setOAuthToken(new OAuthToken($accessToken));

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'authorization_url' => 'http://www.flickr.com/services/oauth/authorize',
            'request_token_url' => 'http://www.flickr.com/services/oauth/request_token',
            'access_token_url' => 'http://www.flickr.com/services/oauth/access_token',

            // Flickr don't use `infos_url`
            'infos_url' => null,

            'perms' => 'read',
        ]);
    }
}
