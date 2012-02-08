<?php

namespace Knp\Bundle\OAuthBundle\Security\Exception;

class AccessTokenAwareExceptionInterface
{
  public function setAccessToken($accessToken);

  public function getAccessToken();
}