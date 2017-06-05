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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;

/**
 * GamewispResourceOwner
 *
 * @author John Madrak <john@madrak.net>
 */
class GamewispResourceOwner extends GenericOAuth2ResourceOwner
{
    /**
     * {@inheritDoc}
     */
    protected $paths = array(
        'identifier' => 'data.id',
        'realname' => array('data.profile.data.first_name', 'data.profile.data.last_name'),
        'nickname' => 'data.username',
    );

    /**
     * {@inheritDoc}
     */
    public function getUserInformation(array $accessToken, array $extraParameters = array())
    {
        if ($this->options['use_bearer_authorization']) {
            $content = $this->httpRequest($this->normalizeUrl($this->options['infos_url'], $extraParameters), null, array('Authorization: Bearer '.$accessToken['access_token']));
        } else {
            $content = $this->doGetUserInformationRequest($this->normalizeUrl($this->options['infos_url'], array_merge(array($this->options['attr_name'] => $accessToken['access_token']), $extraParameters + ['include' => 'profile'])));
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
            'authorization_url'         => 'https://api.gamewisp.com/pub/v1/oauth/authorize',
            'access_token_url'          => 'https://api.gamewisp.com/pub/v1/oauth/token',
            'infos_url'                 => 'https://api.gamewisp.com/pub/v1/user/information',
            'use_bearer_authorization'  => false,
        ));
    }
}
