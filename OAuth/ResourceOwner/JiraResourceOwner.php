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

use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use HWI\Bundle\OAuthBundle\Security\OAuthUtils;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * JiraResourceOwner.
 *
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 */
class JiraResourceOwner extends GenericOAuth1ResourceOwner
{
    /**
     * {@inheritdoc}
     */
    protected $paths = array(
        'identifier' => 'name',
        'nickname' => 'name',
        'realname' => 'displayName',
        'email' => 'emailAddress',
        'profilepicture' => 'avatarUrls.48x48',
    );

    /**
     * {@inheritdoc}
     */
    public function getUserInformation(array $accessToken, array $extraParameters = array())
    {
        $parameters = array_merge(array(
            'oauth_consumer_key' => $this->options['client_id'],
            'oauth_timestamp' => time(),
            'oauth_nonce' => $this->generateNonce(),
            'oauth_version' => '1.0',
            'oauth_signature_method' => $this->options['signature_method'],
            'oauth_token' => $accessToken['oauth_token'],
        ), $extraParameters);

        $parameters['oauth_signature'] = OAuthUtils::signRequest(
            'GET',
            $this->options['infos_session_url'],
            $parameters,
            $this->options['client_secret'],
            $accessToken['oauth_token_secret'],
            $this->options['signature_method']
        );

        $content = $this->getResponseContent($this->doGetUserInformationRequest($this->options['infos_session_url'], $parameters));
        $url = $this->normalizeUrl($this->options['infos_url'], array('username' => $content['name']));

        // Regenerate nonce & signature as URL was changed
        $parameters['oauth_nonce'] = $this->generateNonce();
        $parameters['oauth_signature'] = OAuthUtils::signRequest(
            'GET',
            $url,
            $parameters,
            $this->options['client_secret'],
            $accessToken['oauth_token_secret'],
            $this->options['signature_method']
        );

        $content = $this->doGetUserInformationRequest($url, $parameters);

        $response = $this->getUserResponse();
        $response->setData($content instanceof ResponseInterface ? (string) $content->getBody() : $content);
        $response->setResourceOwner($this);
        $response->setOAuthToken(new OAuthToken($accessToken));

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'authorization_url' => '{base_url}/plugins/servlet/oauth/authorize',
            'request_token_url' => '{base_url}/plugins/servlet/oauth/request-token',
            'access_token_url' => '{base_url}/plugins/servlet/oauth/access-token',

            // JIRA API requires to first know the username to be able to ask for user details
            'infos_session_url' => '{base_url}/rest/auth/1/session',
            'infos_url' => '{base_url}/rest/api/2/user',

            'signature_method' => 'RSA-SHA1',
        ));

        $resolver->setRequired(array(
            'base_url',
        ));

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
