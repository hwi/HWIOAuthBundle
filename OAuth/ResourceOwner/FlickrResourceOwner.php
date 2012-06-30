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
 * FlickrResourceOwner
 *
 * @author Karel <karel@hardware.info>
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
        'infos_url'           => 'http://api.flickr.com/services/rest/?format=json&method=flickr.test.echo&nojsoncallback=1',
        'user_response_class' => '\HWI\Bundle\OAuthBundle\OAuth\Response\PathUserResponse',
        'realm'               => null,
    );

    /**
     * {@inheritDoc}
     */
    protected $paths = array(
        'username'     => 'user_nsid',
        'displayname'  => 'username',
    );
}
