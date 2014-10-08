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
 * EveResourceOwner
 *
 * @author Patrick Ruckstuhl <patrick.ruckstuhl@ch.tario.org>
 */
class EveResourceOwner extends GenericOAuth2ResourceOwner
{
    /**
     * {@inheritDoc}
     */
    protected $paths = array(
        'identifier' => 'CharacterID',
        'nickname'   => 'CharacterName',
        'realname'   => 'CharacterName'
    );

    /**
     * {@inheritDoc}
     */
    protected function configureOptions(OptionsResolverInterface $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'authorization_url' => 'https://login.eveonline.com/oauth/authorize',
            'access_token_url'  => 'https://login.eveonline.com/oauth/token',
            'infos_url'         => 'https://login.eveonline.com/oauth/verify',
            'csrf'              => true
        ));
    }
}
