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
 * TrelloResourceOwner.
 *
 * @author Joseph Bielawski <stloyd@gmail.com>
 */
class TrelloResourceOwner extends GenericOAuth1ResourceOwner
{
    /**
     * {@inheritdoc}
     */
    protected $paths = array(
        'identifier' => 'id',
        'nickname' => 'username',
        'realname' => 'fullName',
        'email' => 'email',
        'profilepicture' => 'avatarSource',
    );

    /**
     * {@inheritdoc}
     */
    public function getAuthorizationUrl($redirectUri, array $extraParameters = array())
    {
        $token = $this->getRequestToken($redirectUri, $extraParameters);

        return $this->normalizeUrl($this->options['authorization_url'], array(
            'scope' => $this->options['scopes'],
            'name' => $this->options['application'],
            'expiration' => $this->options['expiration'],
            'oauth_token' => $token['oauth_token'],
        ));
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'authorization_url' => 'https://trello.com/1/OAuthAuthorizeToken',
            'request_token_url' => 'https://trello.com/1/OAuthGetRequestToken',
            'access_token_url' => 'https://trello.com/1/OAuthGetAccessToken',
            'infos_url' => 'https://api.trello.com/1/members/me?fields=username,fullName,avatarSource,email',
            'realm' => 'trello.com',
            'application' => null,
            'scopes' => 'read',
            'expiration' => null,
        ));
    }
}
