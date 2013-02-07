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

/**
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
 */
class FlickrResourceOwner extends GenericOAuth1ResourceOwner
{
    /**
     * {@inheritDoc}
     */
    protected $options = array(
        'authorization_url'   => 'http://www.flickr.com/services/oauth/authorize',
        'request_token_url'   => 'http://www.flickr.com/services/oauth/request_token',
        'access_token_url'    => 'http://www.flickr.com/services/oauth/access_token',
        'infos_url'           => null,
        'user_response_class' => '\HWI\Bundle\OAuthBundle\OAuth\Response\PathUserResponse',
        'realm'               => null,
        'perms'               => 'read',
    );

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
            'perms'          => $this->getOption('perms'),
            'nojsoncallback' => 1
        );

        return $this->getOption('authorization_url') . '? ' .http_build_query($params);
    }

    /**
     * @param array $accessToken
     *
     * @return \HWI\Bundle\OAuthBundle\OAuth\UserResponseInterface
     *
     * @throws \InvalidArgumentException
     *
     * @todo UserResponseInterface::setResponse() does not make much sense here.
     *       We already have required information parsed. Worth to implement the new UserResponse type.
     */
    public function getUserInformation($accessToken)
    {
        if (!is_array($accessToken)) {
            throw new \InvalidArgumentException(sprintf('%s expects array as its argument', __METHOD__));
        }

        $response = $this->getUserResponse();
        $response->setResponse(json_encode($accessToken));
        $response->setResourceOwner($this);
        $response->setAccessToken($accessToken);

        return $response;
    }
}
