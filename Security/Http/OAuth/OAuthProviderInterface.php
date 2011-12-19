<?php

namespace Knp\OAuthBundle\Security\Http\OAuth;

interface OAuthProviderInterface
{
  public function getUsername($accessToken);
}