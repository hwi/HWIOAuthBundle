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

/**
 * TrelloResourceOwner
 *
 * @author Joseph Bielawski <stloyd@gmail.com>
 */
class TrelloResourceOwner extends GenericOAuth1ResourceOwner
{
    /**
     * {@inheritDoc}
     */
    protected $paths = array(
        'identifier'     => '_id',
        'nickname'       => 'username',
        'realname'       => 'fullName',
        'email'          => 'email',
        'profilepicture' => 'avatarSource',
    );

    /**
     * {@inheritDoc}
     */
    protected function configureOptions(OptionsResolverInterface $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'authorization_url' => 'https://trello.com/1/OAuthAuthorizeToken',
            'request_token_url' => 'https://trello.com/1/OAuthGetRequestToken',
            'access_token_url'  => 'https://trello.com/1/OAuthGetAccessToken',
            'infos_url'         => 'https://api.trello.com/1/members/me?fields=username,fullName,avatarSource,email',
        ));
    }
}
