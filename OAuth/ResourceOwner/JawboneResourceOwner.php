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

use Buzz\Message\RequestInterface as HttpRequestInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * JawboneResourceOwner
 *
 * @author Dmitry Matora <dmitry.matora@gmail.com>
 */
class JawboneResourceOwner extends GenericOAuth2ResourceOwner
{
    /**
     * {@inheritDoc}
     */
    protected $paths = array(
        'xid'               => 'data.id',
        'firstname'         => 'data.first',
        'lastname'          => 'data.last',
        'profilepicture'    => 'data.image',
    );

    /**
     * {@inheritDoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'authorization_url'   => 'https://jawbone.com/auth/oauth2/auth',
            'access_token_url'    => 'https://jawbone.com/auth/oauth2/token',
            'infos_url'           => 'https://jawbone.com/nudge/api/v.1.0/users/@me',
            'use_commas_in_scope' => true,
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function revokeToken($accessToken)
    {
        $response = $this->getInformation($accessToken, 'PartnerAppMembership');

        return 200 === $response->getStatusCode();
    }

    /**
     * {@inheritDoc}
     */
    public function getInformation($accessToken, $type, array $extraParameters = array())
    {
        $url = $this->normalizeUrl($this->options['infos_url'] . '/' . $type, $extraParameters);

        $headers = array(
            'Authorization: Bearer '.$accessToken['access_token'],
            'Accept: application/json',
        );

        return $this->httpRequest($url, null, $headers);
    }
}
