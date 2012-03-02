<?php

namespace HWI\Bundle\OAuthBundle\Security\Exception;

interface AccessTokenAwareExceptionInterface
{
  public function setAccessToken($accessToken);

  public function getAccessToken();
}
