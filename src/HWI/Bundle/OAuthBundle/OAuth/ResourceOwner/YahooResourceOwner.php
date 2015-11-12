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
 * YahooResourceOwner
 *
 * @author Tom <tomilett@gmail.com>
 * @author Alexander <iam.asm89@gmail.com>
 */
class YahooResourceOwner extends GenericOAuth1ResourceOwner
{
    /**
     * {@inheritDoc}
     */
    protected $paths = array(
        'identifier' => 'profile.guid',
        'nickname'   => 'profile.nickname',
        'realname'   => 'profile.givenName',
    );

    /**
     * Override to replace {guid} in the infos_url with the authenticating user's yahoo id
     *
     * {@inheritDoc}
     */
    public function getUserInformation(array $accessToken, array $extraParameters = array())
    {
        $this->options['infos_url'] = str_replace('{guid}', $accessToken['xoauth_yahoo_guid'], $this->options['infos_url']);

        return parent::getUserInformation($accessToken, $extraParameters);
    }

    /**
     * Override to set the Accept header as otherwise Yahoo defaults to XML
     *
     * {@inheritDoc}
     */
    protected function doGetUserInformationRequest($url, array $parameters = array())
    {
        return $this->httpRequest($url, null, $parameters, array('Accept: application/json'));
    }

    /**
     * {@inheritDoc}
     */
    protected function configureOptions(OptionsResolverInterface $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'authorization_url' => 'https://api.login.yahoo.com/oauth/v2/request_auth',
            'request_token_url' => 'https://api.login.yahoo.com/oauth/v2/get_request_token',
            'access_token_url'  => 'https://api.login.yahoo.com/oauth/v2/get_token',
            'infos_url'         => 'https://social.yahooapis.com/v1/user/{guid}/profile',

            'realm'             => 'yahooapis.com',
        ));
    }
}
