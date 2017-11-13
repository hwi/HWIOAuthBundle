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
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * DropboxResourceOwner.
 *
 * @author Jamie Sutherland<me@jamiesutherland.com>
 */
class DropboxResourceOwner extends GenericOAuth2ResourceOwner
{
    /**
     * {@inheritdoc}
     */
    protected $paths = array(
        'identifier' => 'account_id',
        'nickname' => 'email',
        'realname' => 'email',
        'email' => 'email',
    );

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'authorization_url' => 'https://www.dropbox.com/oauth2/authorize',
            'access_token_url' => 'https://api.dropbox.com/oauth2/token',
            'infos_url' => 'https://api.dropboxapi.com/2/users/get_current_account',
        ));
    }

    public function getUserInformation(array $accessToken, array $extraParameters = array())
    {
        if ($this->options['use_bearer_authorization']) {
            $content = $this->httpRequest($this->normalizeUrl($this->options['infos_url'], $extraParameters), 'null',
                array(
                    'Content-type: application/json; charset=UTF-8',
                    'Authorization: Bearer ' . $accessToken['access_token']
                ), 'POST');
        } else {
            $content = $this->doGetUserInformationRequest($this->normalizeUrl($this->options['infos_url'],
                array_merge(array($this->options['attr_name'] => $accessToken['access_token']), $extraParameters)));
        }

        $response = $this->getUserResponse();
        $response->setResponse($content->getContent());

        $response->setResourceOwner($this);
        $response->setOAuthToken(new OAuthToken($accessToken));
        return $response;
    }
}
