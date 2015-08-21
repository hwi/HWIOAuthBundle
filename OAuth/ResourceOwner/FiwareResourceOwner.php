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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Buzz\Message\RequestInterface as HttpRequestInterface;

/**
 * FiwareResourceOwner
 *
 * Resource owner for the fiware keyrock idm oauth 2.0 service
 *
 * more infos at https://github.com/ging/fi-ware-idm/wiki/Using-the-FIWARE-LAB-instance
 *
 * @author Christian Kaspar <christian@sponsoo.de>
 */
class FiwareResourceOwner extends GenericOAuth2ResourceOwner
{
    /**
     * {@inheritDoc}
     */
    protected $paths = array(
        'identifier'     => 'id',
        'nickname'       => 'nickName',
        'realname'       => 'displayName',
        'email'          => 'email',
    );

    /**
     * {@inheritDoc}
     */
    public function getAccessToken(Request $request, $redirectUri, array $extraParameters = array())
    {
        $parameters = array_merge(array(
            'code'          => $request->query->get('code'),
            'grant_type'    => 'authorization_code',
            'redirect_uri'  => $redirectUri,
        ), $extraParameters);

        $headers = array(
            'Authorization: Basic ' . base64_encode($this->options['client_id'] . ':' . $this->options['client_secret']),
            'Content-Type: application/x-www-form-urlencoded',
        );

        $response = $this->httpRequest($this->options['access_token_url'], http_build_query($parameters, '', '&'), $headers, HttpRequestInterface::METHOD_POST);
        $responseContent = $this->getResponseContent($response);

        $this->validateResponseContent($responseContent);

        return $responseContent;
    }

    /**
     * {@inheritDoc}
     */
    public function getUserInformation(array $accessToken, array $extraParameters = array())
    {
        if ($this->options['use_bearer_authorization']) {
            $content = $this->httpRequest($this->normalizeUrl($this->options['infos_url'], array('access_token' => $accessToken['access_token'])), null, array('Authorization: Bearer'));
        } else {
            $content = $this->doGetUserInformationRequest($this->normalizeUrl($this->options['infos_url'], array($this->options['attr_name'] => $accessToken['access_token'])));
        }

        $response = $this->getUserResponse();
        $response->setResponse($content->getContent());

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

        $resolver->setDefaults(array(
            'authorization_url'       => '{base_url}/oauth2/authorize',
            'access_token_url'        => '{base_url}/oauth2/token',
            'revoke_token_url'        => '{base_url}/oauth2/revoke',
            'infos_url'               => '{base_url}/user',
        ));

        $resolver->setRequired(array(
            'base_url',
        ));

        $normalizer = function (Options $options, $value) {
            return str_replace('{base_url}', $options['base_url'], $value);
        };

        $resolver->setNormalizers(array(
            'authorization_url' => $normalizer,
            'access_token_url'  => $normalizer,
            'revoke_token_url'  => $normalizer,
            'infos_url'         => $normalizer,
        ));
    }
}
