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

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * JiraResourceOwner
 *
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 */
class JiraResourceOwner extends GenericOAuth1ResourceOwner
{
    /**
     * {@inheritDoc}
     */
    protected $paths = array(
        'identifier' => 'name',
        'nickname'   => 'name',
        'realname'   => 'displayName',
        'email'      => 'emailAddress',
    );

    /**
     * {@inheritDoc}
     */
    protected function doGetUserInformationRequest($url, array $parameters = array())
    {
        $response = $this->httpRequest($this->options['base_url'].'/rest/auth/1/session', null, $parameters);
        $data     = json_decode($response->getContent(), true);

        return $this->httpRequest($this->normalizeUrl($url, array('username' => $data['name'])), null, $parameters);
    }

    /**
     * {@inheritDoc}
     */
    protected function configureOptions(OptionsResolverInterface $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'base_url'          => '',
            'authorization_url' => '{base_url}/plugins/servlet/oauth/authorize',
            'request_token_url' => '{base_url}/plugins/servlet/oauth/request-token',
            'access_token_url'  => '{base_url}/plugins/servlet/oauth/access-token',
            'infos_url'         => '{base_url}/rest/api/2/user',

            'signature_method'  => 'RSA-SHA1',
        ));

        $normalizer = function (Options $options, $value) {
            return str_replace('{base_url}', $options['base_url'], $value);
        };

        $resolver->setNormalizers(array(
            'authorization_url' => $normalizer,
            'request_token_url' => $normalizer,
            'access_token_url'  => $normalizer,
            'infos_url'         => $normalizer,
        ));
    }
}
