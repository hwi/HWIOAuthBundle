<?php
/**
 * Created by PhpStorm.
 * User: andreaquintino
 * Date: 17/01/19
 * Time: 17.18
 */

namespace HWI\Bundle\OAuthBundle\OAuth\ResourceOwner;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * KeycloakResourceOwner.
 *
 * @author Andrea Quintino <andreaquin1990@gmail.com>
 */
class KeycloakResourceOwner extends GenericOAuth2ResourceOwner
{

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
          ->setRequired('realms')
          ->setRequired('protocol')
          ->setDefault('protocol', 'openid-connect');
    }
}