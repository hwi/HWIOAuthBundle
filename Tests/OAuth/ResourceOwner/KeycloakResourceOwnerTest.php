<?php
/**
 * Created by PhpStorm.
 * User: andreaquintino
 * Date: 17/01/19
 * Time: 17.12
 */

namespace HWI\Bundle\OAuthBundle\Tests\OAuth\ResourceOwner;

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\KeycloakResourceOwner;

class KeycloakResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected $resourceOwnerClass = KeycloakResourceOwner::class;
}
