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
 * @author Martin Aarhof <martin.aarhof@gmail.com>
 */
class RedditResourceOwner extends GenericOAuth2ResourceOwner
{
    /**
     * {@inheritdoc}
     */
    protected $paths = array(
        'identifier' => 'id',
        'nickname' => 'name',
        'realname' => null,
        'email' => null,
    );

    /**
     * {@inheritdoc}
     */
    protected function doGetTokenRequest($url, array $parameters = array())
    {
        return $this->httpRequest(
            $url,
            http_build_query($parameters, '', '&'),
            [
                'Authorization' => 'Basic '.base64_encode(sprintf('%s:%s', $this->options['client_id'], $this->options['client_secret'])),
            ],
            'POST'
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'authorization_url' => 'https://ssl.reddit.com/api/v1/authorize',
            'access_token_url' => 'https://ssl.reddit.com/api/v1/access_token',
            'infos_url' => 'https://oauth.reddit.com/api/v1/me.json',

            'use_bearer_authorization' => true,
            'use_commas_in_scope' => true,
            'csrf' => true,
            'scope' => 'identity',

            'duration' => 'permanent',
        ]);
    }
}
