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

use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;

/**
 * AzureResourceOwner
 *
 * @author Baptiste Clavié <clavie.b@gmail.com>
 */
class AzureResourceOwner extends GenericOAuth2ResourceOwner
{
    /**
     * {@inheritDoc}
     */
    protected $paths = array(
        'identifier'     => 'sub',
        'nickname'       => 'unique_name',
        'realname'       => array('given_name', 'family_name'),
        'email'          => array('upn', 'email'),
        'profilepicture' => null,
    );

    /**
     * {@inheritDoc}
     */
    public function configure()
    {
        $this->options['access_token_url'] = sprintf($this->options['access_token_url'], $this->options['application']);
        $this->options['authorization_url'] = sprintf($this->options['authorization_url'], $this->options['application']);
    }

    /**
     * {@inheritDoc}
     */
    public function getAuthorizationUrl($redirectUri, array $extraParameters = array())
    {
        return parent::getAuthorizationUrl($redirectUri, $extraParameters + array('resource' => $this->options['resource']));
    }

    /**
     * {@inheritDoc}
     */
    public function refreshAccessToken($refreshToken, array $extraParameters = array())
    {
        return parent::refreshAccessToken($refreshToken, $extraParameters + array('resource' => $this->options['resource']));
    }

    /**
     * {@inheritDoc}
     */
    public function getUserInformation(array $accessToken, array $extraParameters = array())
    {
        // from http://stackoverflow.com/a/28748285/624544
        list(, $jwt, ) = explode('.', $accessToken['id_token'], 3);

        // if the token was urlencoded, do some fixes to ensure that it is valid base64 encoded
        $jwt = str_replace('-', '+', $jwt);
        $jwt = str_replace('_', '/', $jwt);

        // complete token if needed
        switch (strlen($jwt) % 4) {
            case 0:
            break;

            case 2:
                $jwt .= '=';

            case 3:
                $jwt .= '=';
            break;

            default:
                throw new \InvalidArgumentException('Invalid base64 format sent back');
        }

        $response = $this->getUserResponse();
        $response->setResponse(base64_decode($jwt));

        $response->setResourceOwner($this);
        $response->setOAuthToken(new OAuthToken($accessToken));

        return $response;
    }

    /**
     * {@inheritDoc}
     */
    protected function configureOptions(OptionsResolverInterface $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setRequired(array('resource'));

        $resolver->setDefaults(array(
            'infos_url' => '',
            'authorization_url' => 'https://login.windows.net/%s/oauth2/authorize',
            'access_token_url' => 'https://login.windows.net/%s/oauth2/token',

            'application' => 'common',
            'api_version' => 'v1.0',
            'csrf' => true
        ));
    }
}
