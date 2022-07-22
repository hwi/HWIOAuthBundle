<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\OAuth\ResourceOwner;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Baptiste Clavié <clavie.b@gmail.com>
 */
final class AzureResourceOwner extends GenericOAuth2ResourceOwner
{
    public const TYPE = 'azure';

    /**
     * {@inheritdoc}
     */
    protected array $paths = [
        'identifier' => 'sub',
        'nickname' => 'unique_name',
        'lastname' => 'family_name',
        'firstname' => 'given_name',
        'realname' => ['given_name', 'family_name'],
        'email' => ['upn', 'email'],
        'profilepicture' => null,
    ];

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this->options['access_token_url'] = sprintf($this->options['access_token_url'], $this->options['application']);
        $this->options['authorization_url'] = sprintf($this->options['authorization_url'], $this->options['application']);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException
     */
    public function getUserInformation(array $accessToken, array $extraParameters = [])
    {
        // from http://stackoverflow.com/a/28748285/624544
        [, $jwt] = explode('.', \array_key_exists('id_token', $accessToken) ? $accessToken['id_token'] : $accessToken['access_token'], 3);

        // if the token was urlencoded, do some fixes to ensure that it is valid base64 encoded
        $jwt = str_replace(['-', '_'], ['+', '/'], $jwt);

        // complete token if needed
        switch (\strlen($jwt) % 4) {
            case 0:
                break;

            case 2:
            case 3:
                $jwt .= '=';
                break;

            default:
                throw new \InvalidArgumentException('Invalid base64 format sent back');
        }

        $response = parent::getUserInformation($accessToken, $extraParameters);
        $response->setData(base64_decode($jwt));

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'infos_url' => 'https://graph.microsoft.com/v1.0/me',
            'authorization_url' => 'https://login.microsoftonline.com/%s/oauth2/v2.0/authorize',
            'access_token_url' => 'https://login.microsoftonline.com/%s/oauth2/v2.0/token',
            'application' => 'common',
            'api_version' => 'v1.0',
            'csrf' => true,
        ]);
    }
}
