<?php

namespace HWI\Bundle\OAuthBundle\OAuth\ResourceOwner;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DiscogsResourceOwner extends GenericOAuth1ResourceOwner
{
    /**
     * {@inheritDoc}
     */
    protected $paths = array(
        'identifier'     => 'id',
        'nickname'       => 'username'
    );

    /**
     * {@inheritDoc}
     */
    protected function configureOptions(OptionsResolverInterface $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'authorization_url' => 'https://www.discogs.com/oauth/authorize',
            'request_token_url' => 'https://api.discogs.com/oauth/request_token',
            'access_token_url'  => 'https://api.discogs.com/oauth/access_token',
            'infos_url'         => 'https://api.discogs.com/oauth/identity',
        ));
    }
} 
