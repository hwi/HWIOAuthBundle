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

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * TwitterResourceOwner
 *
 * @author Alexander <iam.asm89@gmail.com>
 */
class TwitterResourceOwner extends GenericOAuth1ResourceOwner
{
    /**
     * {@inheritDoc}
     */
    protected $paths = array(
        'identifier'     => 'id_str',
        'nickname'       => 'screen_name',
        'realname'       => 'name',
        'profilepicture' => 'profile_image_url_https',
    );

    /**
     * {@inheritDoc}
     */
    protected function setupOptions(OptionsResolver $resolver)
    {
        parent::setupOptions($resolver);

        $resolver->setDefaults(array(
            'authorization_url' => 'https://api.twitter.com/oauth/authenticate',
            'request_token_url' => 'https://api.twitter.com/oauth/request_token',
            'access_token_url'  => 'https://api.twitter.com/oauth/access_token',
            'infos_url'         => 'https://api.twitter.com/1.1/account/verify_credentials.json',
        ));

        $resolver->setOptional(array(
            'x_auth_access_type',
        ));
        $resolver->setAllowedValues(array(
            // @link https://dev.twitter.com/oauth/reference/post/oauth/request_token
            'x_auth_access_type' => array('read', 'write'),
        ));
    }
}
