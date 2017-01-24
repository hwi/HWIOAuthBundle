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
 * TwitterResourceOwner.
 *
 * @author Alexander <iam.asm89@gmail.com>
 */
class TwitterResourceOwner extends GenericOAuth1ResourceOwner
{
    /**
     * {@inheritdoc}
     */
    protected $paths = array(
        'identifier' => 'id_str',
        'nickname' => 'screen_name',
        'realname' => 'name',
        'profilepicture' => 'profile_image_url_https',
        'email' => 'email',
    );

    /**
     * {@inheritdoc}
     */
    public function getUserInformation(array $accessToken, array $extraParameters = array())
    {
        if ($this->options['include_email']) {
            $this->options['infos_url'] = $this->normalizeUrl($this->options['infos_url'], array('include_email' => 'true'));
        }

        return parent::getUserInformation($accessToken, $extraParameters);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'authorization_url' => 'https://api.twitter.com/oauth/authenticate',
            'request_token_url' => 'https://api.twitter.com/oauth/request_token',
            'access_token_url' => 'https://api.twitter.com/oauth/access_token',
            'infos_url' => 'https://api.twitter.com/1.1/account/verify_credentials.json',
            'include_email' => false,
        ));

        $resolver->setDefined('x_auth_access_type');
        // @link https://dev.twitter.com/oauth/reference/post/oauth/request_token
        $resolver->setAllowedValues('x_auth_access_type', array('read', 'write'));
        // @link https://dev.twitter.com/rest/reference/get/account/verify_credentials
        $resolver->setAllowedTypes('include_email', 'bool');
    }
}
