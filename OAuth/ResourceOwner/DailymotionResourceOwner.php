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
 * DailymotionResourceOwner
 *
 * @author Joseph Bielawski <stloyd@gmail.com>
 */
class DailymotionResourceOwner extends GenericOAuth2ResourceOwner
{
    /**
     * {@inheritDoc}
     */
    protected $options = array(
        'authorization_url' => 'https://api.dailymotion.com/oauth/authorize',
        'access_token_url'  => 'https://api.dailymotion.com/oauth/token',
        'infos_url'         => 'https://api.dailymotion.com/me',

        // @link http://www.dailymotion.com/doc/api/authentication.html#dialog-form-factors
        'display'           => null,
    );

    /**
     * {@inheritDoc}
     */
    protected $paths = array(
        'identifier'     => 'id',
        'nickname'       => 'screenname',
        'realname'       => 'fullname', // requires 'userinfo' scope
        'email'          => 'email', // requires 'email' scope
        'profilepicture' => 'avatar_medium_url'
    );

    /**
     * {@inheritDoc}
     */
    public function getAuthorizationUrl($redirectUri, array $extraParameters = array())
    {
        return parent::getAuthorizationUrl($redirectUri, array_merge(array('display' => $this->getOption('display')), $extraParameters));
    }
}
