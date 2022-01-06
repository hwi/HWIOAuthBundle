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

use HWI\Bundle\OAuthBundle\OAuth\Exception\HttpTransportException;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use HWI\Bundle\OAuthBundle\Security\Helper\NonceGenerator;
use HWI\Bundle\OAuthBundle\Security\OAuthUtils;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 */
final class JiraResourceOwner extends GenericOAuth1ResourceOwner
{
    public const TYPE = 'jira';

    /**
     * {@inheritdoc}
     */
    protected array $paths = [
        'identifier' => 'name',
        'nickname' => 'name',
        'realname' => 'displayName',
        'email' => 'emailAddress',
        'profilepicture' => 'avatarUrls.48x48',
    ];

    /**
     * {@inheritdoc}
     */
    public function getUserInformation(array $accessToken, array $extraParameters = [])
    {
        $parameters = array_merge([
            'oauth_consumer_key' => $this->options['client_id'],
            'oauth_timestamp' => time(),
            'oauth_nonce' => NonceGenerator::generate(),
            'oauth_version' => '1.0',
            'oauth_signature_method' => $this->options['signature_method'],
            'oauth_token' => $accessToken['oauth_token'],
        ], $extraParameters);

        $parameters['oauth_signature'] = OAuthUtils::signRequest(
            'GET',
            $this->options['infos_session_url'],
            $parameters,
            $this->options['client_secret'],
            $accessToken['oauth_token_secret'],
            $this->options['signature_method']
        );

        $content = $this->getResponseContent($this->doGetUserInformationRequest($this->options['infos_session_url'], $parameters));
        $url = $this->normalizeUrl($this->options['infos_url'], ['username' => $content['name']]);

        // Regenerate nonce & signature as URL was changed
        $parameters['oauth_nonce'] = NonceGenerator::generate();
        $parameters['oauth_signature'] = OAuthUtils::signRequest(
            'GET',
            $url,
            $parameters,
            $this->options['client_secret'],
            $accessToken['oauth_token_secret'],
            $this->options['signature_method']
        );

        try {
            $content = $this->doGetUserInformationRequest($url, $parameters);

            $response = $this->getUserResponse();
            $response->setData($content->getContent(false));
            $response->setResourceOwner($this);
            $response->setOAuthToken(new OAuthToken($accessToken));

            return $response;
        } catch (TransportExceptionInterface $e) {
            throw new HttpTransportException('Error while sending HTTP request', $this->getName(), $e->getCode(), $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'authorization_url' => '{base_url}/plugins/servlet/oauth/authorize',
            'request_token_url' => '{base_url}/plugins/servlet/oauth/request-token',
            'access_token_url' => '{base_url}/plugins/servlet/oauth/access-token',

            // JIRA API requires to first know the username to be able to ask for user details
            'infos_session_url' => '{base_url}/rest/auth/1/session',
            'infos_url' => '{base_url}/rest/api/2/user',

            'signature_method' => 'RSA-SHA1',
        ]);

        $resolver->setRequired([
            'base_url',
        ]);

        $normalizer = function (Options $options, $value) {
            return str_replace('{base_url}', $options['base_url'], $value);
        };

        $resolver
            ->setNormalizer('authorization_url', $normalizer)
            ->setNormalizer('request_token_url', $normalizer)
            ->setNormalizer('access_token_url', $normalizer)
            ->setNormalizer('infos_session_url', $normalizer)
            ->setNormalizer('infos_url', $normalizer)
        ;
    }
}
